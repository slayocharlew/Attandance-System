<?php
  require_once('defines.php');

  class Node {

    /****   Properties   ******/
    const rollNumberRegex = '/([0-9]+)(\/[a-zA-Z]+\/[0-9]{2})/';
    private $teacher;      // teacher_id
    private $subjectCode;
    private $section;
    private $year;
    private $semester;
    private $numberOfDays;
    private $records = array(); // array of key value paired for each student

    /********** Method ***********/

    # CONSTRUCTORS

    function __construct() {

      // constructor to allow multiple constructors with different number of arguments
      $a = func_get_args();
      $i = func_num_args();
      if (method_exists($this,$f='__construct'.$i)) {
        call_user_func_array(array($this,$f),$a);
      }
    }
    function __construct7($code,$teacher_uid,$year,$semester,$section,$start,$end) {
      $this->setCode($code);
      $this->setTeacher($teacher_uid);
      $this->setYear($year);
      $this->setSemester($semester);
      $this->setSection($section);
      $this->setDays(0);
      $this->initRecords($start,$end);
      if($this->saveNode() === false) {
        echo false;
      }
    }


    # Helper Functions

    public function retrieveObject($code,$section,$year) {

      $con = connectTo();

      $code = sqlReady($code);
      $section = sqlReady($section);
      $year = sqlReady($year);

      if($con->connect_errno) {
      	return false;
      } else {
        $obj = $con->query('select object from objects where code = "'.$code.'" and section = "'.$section.'" and year = "'.$year.'"');
        if($con->errno) {
          return false;
        } else {
            if($obj->num_rows == 1) {
              $obj = $obj->fetch_assoc()['object'];
              return unserialize($obj);
            } else {
              return false;
            }
        }
      }
    }
    public function retrieveObjecti($class_id,$teacher_uid) {

      $con = connectTo();

      $class_id = sqlReady($class_id);
      $teacher_uid = sqlReady($teacher_uid);

      if($con->connect_errno) {
      	return false;
      } else {
        $obj = $con->query('select object from objects where uid = "'.$class_id.'" and teacher_uid = "'.$teacher_uid.'"');
        if($con->errno) {
          return false;
        } else {
            if($obj->num_rows == 1) {
              $obj = $obj->fetch_array()['object'];
              return unserialize($obj);
            } else {
              return false;
            }
        }
      }
    }
    public function initRecords($start,$end) {

      if(verify(ROLL,$start) === false) return false;
      if(verify(ROLL,$end) === false) return false;

      $s = preg_replace(self::rollNumberRegex,"$1",$start);
      $e = preg_replace(self::rollNumberRegex,"$1",$end);
      $type = preg_replace(self::rollNumberRegex,"$2",$start);

      # TO UPDATING THE RECORDS ARRAY
      foreach(range($s,$e) as $d) {
        $this->records[$d.$type] = array('present'=>0,'timeline'=>array()); 
      }
    }
    public function deleteNode() {
    /*
      Delete the mode from the database
    */
      $con = connectTo();
      if($con->connect_errno) {
      	return false;
      } else {
        $teacher_uid = $this->getTeacherID();
        $code = $this->getCode();
        $section = $this->getSection();
        $year = $this->getYear();
        $obj = $con->query('delete from objects where teacher_uid = "'.$teacher_uid.'" and code = "'.$code.'" and section = "'.$section.'" and year = "'.$year.'"');
        if($obj && $con->affected_rows) {
          return true;
        } else {
          return false;
        }
      }
    }

    public function saveNode() {
    /*
    save the data into the database
    */
      $con = connectTo();
      if($con->connect_errno) {
      	return false;
      } else {
      $teacher_uid = $this->getTeacherID();
      $code = $this->getCode();
      $section = $this->getSection();
      $year = $this->getYear();
      $obj = $con->query('select object from objects where teacher_uid = "'.$teacher_uid.'" and code = "'.$code.'" and section = "'.$section.'" and year = "'.$year.'"');
      if($obj->num_rows)
        $obj = $con->query('update objects set object = "'.$con->real_escape_string(serialize($this)).'" where teacher_uid = "'.$teacher_uid.'" and code = "'.$code.'" and section = "'.$section.'" and year = "'.$year.'"');
      else
        $obj = $con->query($q= 'insert into `objects`(`teacher_uid`, `code`, `year`, `section`, `object`) VALUES ("'.$teacher_uid.'","'.$code.'","'.$year.'","'.$section.'","'.$con->real_escape_string(serialize($this)).'")');
        if($con->errno) {
          return false;
        } else {
          return true;
        }
      }
      return false;
    }

    public function saveNodei($class_id) {
    /*
      save the data into the database in the mode of class_id
    */
      $con = connectTo();
      if($con->connect_errno) {
      	return false;
      } else {
        $teacher_uid = $this->getTeacherID();
        $code = $this->getCode();
        $section = $this->getSection();
        $year = $this->getYear();
        $selectedNode = $con->query('select object from objects where uid = '.$class_id.' and  teacher_uid = '.$teacher_uid);
        if($selectedNode && $con->affected_rows) {
          $obj = $con->query('update objects set code = "'.$code.'", year = "'.$year.'", section = "'.$section.'", object = "'.$con->real_escape_string(serialize($this)).'" where teacher_uid = "'.$teacher_uid.'" and uid = "'.$class_id.'"');
          if($obj && $con->affected_rows) {
            return true;
          }
          return false;
        }
        return false;
      }
    }

    public function isPresent($rollNumber,$newPresents) {
    /*
     Takes -> Roll number and new number of present days
     Does -> Tells if that student was present or not
     Returns -> 1 if present , 0 if absent , false if not found
    */
      if(isset($this->records[$rollNumber]))
        return ( $this->records[$rollNumber]['present'] < $newPresents )? 1 : 0;
      return false;
    }

    public function deleteRoll($rollNumber) {
    /*
     delete roll number
    */
      if(isset($this->records[$rollNumber])) {
        unset($this->records[$rollNumber]);
        return true;
      }
      return false;
    }


    /**********  GETTERS   **********/

    public function getTeacherID() {
      return $this->teacher;
    }
    public function getTeacherName() {
      $con = connectTo();
      $s = $con->query("select name from teacher where uid = ".$this->getTeacherID());
      $name = $s->fetch_assoc();
      $name = $name['name'];
      return $name;
    }

    public function getCode() {
    /*
     Class code
    */
      return $this->subjectCode;
    }

    public function getYear() {
    /*
     Year of class
    */
      return $this->year;
    }

    public function getSemester() {
    /*
     Semester of class
    */
      return $this->semester;
    }

    public function getSection() {
    /*
     Section of class
    */
      return $this->section;
    }

    public function getDays() {
    /*
     Days conducted by teacher for the class
    */
      return $this->numberOfDays;
    }

    public function getPercent($rollNumber) {
    /*
    Get percentage and return it
    */
      return isset($this->records[$rollNumber])?(100*($this->records[$rollNumber]['present']/$this->getDays())):false;
    }

    public function getTimeline($rollNumber) {
    /*
    TimeLine for the roll number
    */
      return isset($this->records[$rollNumber])?$this->records[$rollNumber]['timeline']:false;
    }

    public function getRecords() {
    /*
     Returns the records for entire object
    */
      return $this->records;
    }

    /**************   SETTERS  **************/

    public function setTeacher($val) {
    /*
     Teacher Value
    */
      $this->teacher = $val;
    }

    public function setCode($val) {
    /*
     Code Value
    */
      $this->subjectCode = $val;
    }

    public function setYear($val) {
    /*
     Year Value
    */
      $this->year = $val;
    }

    public function setSemester($val) {
    /*
     Semester Value
    */
      $this->semester = $val;
    }
    public function setSection($val) {
    /*
     Section Value
    */
      $this->section = $val;
    }

    public function setDays($val) {
    /*
     Day Value
    */
      $this->numberOfDays = $val;
    }

    public function setPresence($rollNumber,$newPresents,$timestamp) {
    /*
     Roll Number, New Presents , Timestamp
    */
      if(isset($this->records[$rollNumber])) {
        $this->records[$rollNumber]['timeline'][$timestamp] =  $this->isPresent($rollNumber,$newPresents);
        $this->records[$rollNumber]['present'] = $newPresents;
      } else
        return false;
    }
  }
?>