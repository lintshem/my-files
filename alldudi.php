<?php

header('Access-Control-Allow-Origin:*');
$NO='1400323';
$result = array("status" => 'ok', 'error' => '', 'data' => '');
$con = mysqli_connect('localhost', $NO, 'Teddy6910', $NO );

if (mysqli_connect_error()) {
    $result['error'] = 'no connection';
} else {
    if (isset($_POST['getall'])) {
        $query = "SELECT * FROM notice";
        $res = mysqli_query($con, $query);
        if (mysqli_error($con)) {
            $result['error'] = mysqli_error($con);
        } else {
            $rows = mysqli_fetch_all($res);
            $result['data'] = $rows;
            $result['status'] = 'ok';
        }
    } else if (isset($_POST['setall'])) {
        $add = $_POST['setall'];
        $add = json_decode($add);
        if (!is_array($add)) {
            $result['error'] = 'not array';
        } else {
            $delquery = "DELETE FROM notice ";
            $delres = mysqli_query($con, $delquery);
            $result['data'] .= $delres;
            foreach ($add as $row) {
                $id = $row[0];
                $desc = $row[1];
                $date = $row[2];
                $file = $row[3];
                $query = "INSERT INTO notice (description,date,file) VALUES ('{$desc}','{$date}','{$file}')";
                $res = mysqli_query($con, $query);
                if (mysqli_error($con)) {
                    $result['error'] .= "error:{$id},";
                } else {
                    $result['data'] .= "done:{$id}";
                }
            }

            $result['status'] = 'ok';
        }
    } else if (isset($_POST['uploadfile'])) {
        $name = $_POST['uploadfile'];
        if (!isset($_FILES[$name])) {
            $result['erro'] = 'no file';
        } else {
            $suc = move_uploaded_file($_FILES[$name]['tmp_name'], "assets/data/{$_FILES[$name]['name']}");
            if ($suc) {
                $result['data'] = 'suc';
                $result['status'] = 'ok';
            } else {
                $result['error'] = 'not moved';
            }
        }
    } else if (isset($_POST['oldsetresult'])) {
        $data = json_decode($_POST['result']);
        if (!is_array($data)) {
            $result['error'] = 'not array';
        } else {
            foreach ($data as $row) {
                $id = $row[0];
                $studid = $row[1];
                $form = $row[2];
                $str = $row[3];
                $sub = $row[4];
                $points = $row[5];
                $aver = $row[6];
                $sno = $row[7];
                $ono = $row[8];
                if ($row[0] == -1) {
                    $query = "INSERT INTO result (studid,form,stream,subno,points,average,strno,overno) 
                    VALUES ('{$studid}','{$form}','{$str}','{$sub}','{$points}','{$aver}','{$sno}','{$ono}')";
                    $res = mysqli_query($con, $query);
                    echo $res . 'insert ';
                } else if ($row[0] == -2) {
                    $ids = join("', '", $row[2]);
                    $ids = "('{$ids}')";
                    $query = "DELETE FROM result WHERE id in {$ids}";
                    $res = mysqli_query($con, $query);
                    $result['data'] = $res;
                    $result['status'] = 'ok';
                } else {
                    $query = "UPDATE result SET studid='{$studid}', form='{$form}',stream='{$str}',subno='{$sub}',
                    points='{$points}',average='{$aver}',strno='{$sno}',overno='{$ono}' WHERE id='{$id}' ";
                    $res = mysqli_query($con, $query);
                    echo $res . 'update ';
                }
            }
        }
    } else if (isset($_POST['getresult'])) {
        $sid = $_POST['getresult'];
        $query = "SELECT * FROM result WHERE studid='{$sid}'";
        $res = mysqli_query($con, $query);
        if (mysqli_error(($con))) {
            $result['error'] = 'query error';
        } else {
            $rows = mysqli_fetch_all($res);
            $result['data'] = $rows;
            $result['status'] = 'ok';
        }
    } else if (isset($_POST['getstudent'])) {
        $data = json_decode($_POST['getstudent']);
        if (!is_object($data)) {
            $result['error'] = 'not object';
        } else {
            $name = $data->name;
            $password = $data->password;
            $query = "SELECT * FROM students WHERE admission='{$name}' AND password='{$password}'";
            $res = mysqli_query($con, $query);
            if (mysqli_error($con)) {
                $result['error'] = mysqli_error($con);
            } else {
                $row = mysqli_fetch_row($res);
                $result['data'] = $row;
                $result['status'] = 'ok';
            }
        }
    } else if (isset($_POST['getstudents'])) {
        $data = json_decode($_POST['getstudents']);
        if (!is_object($data)) {
            $result['error'] = 'not object';
        } else {
            $adm = $data->admission;
            $last = $adm + $data->pages;
            $query = "SELECT admission,name,password,form,stream,billed,paid,balance,installments FROM students WHERE admission BETWEEN '{$adm}' AND '{$last}' ORDER BY admission";
            $res = mysqli_query($con, $query);
            if (mysqli_error($con)) {
                $result['error'] = mysqli_error($con);
            } else {
                $rows = mysqli_fetch_all($res);
                $result['data'] = $rows;
            }
        }
    } else if (isset($_POST['setstudents'])) {
        $data = json_decode($_POST['setstudents']);
        if (!is_array($data)) {
            $result['error'] = 'not array';
        } else {
            $done = array();
            foreach ($data as $row) {
                $adm = $row[0];
                $e_query = "SELECT if(EXISTS(SELECT * FROM students WHERE admission='{$adm}'),'YES','NO')";
                $e_res = mysqli_query($con, $e_query);
                if (mysqli_error($con)) {
                    $result['error'] .= mysqli_error($con);
                } else { //["0","sharon","mongina","234","form","jubilants","21300","15600","6700","4"]
                    $adm = $row[0];
                    $name = $row[1];
                    $pass = $row[2];
                    $form = $row[3];
                    $str = $row[4];
                    $bill = $row[5];
                    $paid = $row[6];
                    $bal = $row[7];
                    $ins = $row[8];

                    $yesno = mysqli_fetch_row($e_res)[0];
                    $query = '';
                    if ($yesno == 'YES') {
                        $query = "UPDATE students SET name='{$name}',password='{$pass}',form='{$form}',stream='{$str}',billed='{$bill}',paid='{$paid}',balance='{$bal}',installments='{$ins}' WHERE admission='{$adm}'";
                    } else {
                        $query = "INSERT INTO students (admission,name,password,form,stream,billed,paid,balance,installments) VALUES ('{$adm}','{$name}','{$pass}','{$form}','{$str}','{$bill}','{$paid}','{$bal}','{$ins}')";
                    }
                    $res = mysqli_query($con, $query);
                    if (mysqli_error($con)) {
                        $result['error'] .= ' error ' . $adm;
                    } else {
                        array_push($done, $adm);
                    }
                }
            }
            $result['data'] = $done;
        }
    } else if (isset($_POST['deletestudents'])) {
        $data = json_decode($_POST['deletestudents']);
        $query = '';
        if ($data->range) {
            $start = $data->start;
            $end = $data->end;
            $query = "DELETE FROM students WHERE admission BETWEEN '{$start}' AND '{$end}' ";
        } else {
            $set = $data->set;
            $query = "DELETE FROM students WHERE admission IN {$set}";
        }
        $res = mysqli_query($con, $query);
        if (mysqli_error($con)) {
            $result['error'] = mysqli_error($con);
        } else {
            $result['data'] = $res;
            $result['status'] = 'ok';
        }
    } else if (isset($_POST['setresultsafe'])) {
        $data = json_decode($_POST['setresultsafe']);
        if (!is_array($data)) {
            $result['error'] = 'not array';
        } else {
            $done = array();
            foreach ($data as $row) {
                $adm = $row[0];
                $form = $row[1];
                $str = $row[2];
                $sub = $row[3];
                $points = $row[4];
                $aver = $row[5];
                $sno = $row[6];
                $ono = $row[7];
                $query = "INSERT INTO result (studid,form,stream,subno,points,average,strno,overno) VALUES ('{$adm}','{$form}','{$str}','{$sub}','{$points}','{$aver}','{$sno}','{$ono}')";
                $res = mysqli_query($con, $query);
                if (mysqli_error($con)) {
                    $result['error'] .= ' error ' . $adm;
                } else {
                    array_push($done, $adm);
                }
            }
            //remove duplicates
            $rquery = "SELECT studid,form,stream,subno,points,average,strno,overno FROM result";
            $res = mysqli_query($con, $rquery);
            if (mysqli_error($con)) {
                $result['error'] .= 'Could not remove duplicates';
            } else {
                $rows = mysqli_fetch_all($res);
                $clean = array();
                foreach ($rows as $row) {
                    $found = false;
                    foreach ($clean as $c) {
                        if (($c[0] == $row[0]) && ($c[1] == $row[1])) {
                            if ($c[2] == $row[2]) {
                                $found = true;
                            }
                        }
                    }
                    if (!$found) {
                        array_push($clean, $row);
                    }
                }
                $added = count($clean);
                $dups = count($rows) - $added;
                $delquery = "DELETE FROM result";
                $res = mysqli_query($con, $delquery);
                $data = array("added" => $added, "dups" => $dups, "res" => $res);
                $result['data'] = $data;
                foreach ($clean as $row) {
                    $adm = $row[0];
                    $form = $row[1];
                    $str = $row[2];
                    $sub = $row[3];
                    $points = $row[4];
                    $aver = $row[5];
                    $sno = $row[6];
                    $ono = $row[7];
                    $query = "INSERT INTO result (studid,form,stream,subno,points,average,strno,overno) VALUES ('{$adm}','{$form}','{$str}','{$sub}','{$points}','{$aver}','{$sno}','{$ono}')";
                    $res = mysqli_query($con, $query);
                }
            }
        }
    } else if (isset($_POST['setresultwithdelete'])) {
        $wrapper = json_decode($_POST['setresultwithdelete']);
        $data = $wrapper->data;
        $adm = $wrapper->adm;
        if (!is_array($data)) {
            $result['error'] = 'not array';
        } else {
            $done = array();
            $res = mysqli_query($con, "DELETE FROM result WHERE studid='{$adm}'");
            foreach ($data as $row) {
                $adm = $row[0];
                $form = $row[1];
                $str = $row[2];
                $sub = $row[3];
                $points = $row[4];
                $aver = $row[5];
                $sno = $row[6];
                $ono = $row[7];

                $query = "INSERT INTO result (studid,form,stream,subno,points,average,strno,overno) VALUES ('{$adm}','{$form}','{$str}','{$sub}','{$points}','{$aver}','{$sno}','{$ono}')";
                $res = mysqli_query($con, $query);
                if (mysqli_error($con)) {
                    $result['error'] .= ' error ' . $adm;
                } else {
                    array_push($done, $adm);
                }
            }
            $result['data'] = $done;
        }
    
    
    
    
    }else if(isset($_POST['uploadpic'])){
        $name=$_POST['uploadpic'];
        if(isset($_FILES['file'])){
            $tmpfile=$_FILES['file'];
            $res=move_uploaded_file($tmpfile['tmp_name'],"assets/images/{$name}");
            $result['data']=$res;
        }

    }else if(isset($_POST['getcsv'])){
        $table=$_POST['getcsv'];
        $query="";
        //if($table=='result')
        $query="SELECT studid,form,stream,subno,points,average,strno,overno FROM result";
        // if($table=="students")
        // $query="SELECT admission,name,password,form,stream,billed,paid,balance,installments FROM students";
        $res=mysqli_query($con,$query);
        $rows=mysqli_fetch_all($res);
        $result['data']=$rows;
        

    } else {
        $result['error'] = 'nodata';
    }
}

echo json_encode($result);
