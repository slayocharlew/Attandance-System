//function to verify teacher Email match
function verify(type,input) {
  var regexes = {'email':/^([\S]+)@([\S]+)\.([\S]+)$/,'phone':/^[0-9]{10}$/,'code':/^([a-zA-Z]{3})\-([0-9]{3})$/,'roll':/^([0-9]{3})\/([a-zA-z]{2})\/([0-9]{2})$/,'name':/^[a-zA-Z \']+$/,'num':/^[0-9]+$/};
  return ((input.match(regexes[type]) == null)? false:true);
}

//add class, delete warning  and delete class
$(document).ready(function() {
  $('#cancel').click(function() {
    $('.modal').modal('hide');
  });
 $('#add').click(function() {
    addTheClass();
  });
  $('.delete-class-warning').click(function() {
    deleteWarning($(this).parent());
  });
  $('.delete-class-code').click(function() {
    deleteClass($(this).parent());
  });
});

//function for delete warning
function deleteWarning(handle) {
  code = handle.find('.code').text();
  section = handle.find('.section').text();
  year = handle.find('.year').text();
  $('.warning-class').html('<span class="warning-code">'+code+'</span> ( <span class="warning-section">'+section+'</span> ) '+' , <span class="warning-year">'+year+'</span>');
}

//function to delete class
function deleteClass(handle) {
  code = handle.find('.warning-code').text();
  section = handle.find('.warning-section').text();
  year = handle.find('.warning-year').text();
  $.ajax({
    url : 'php/delete_class.php', //link file for delete class
    type : 'post',
    data : {code:code,year:year,section:section},
    dataType : 'json',
    success : function(r) {
      switch(r.error) {
        case 'not_found' :
          alert('No such class found! Logging you out for security');
          window.location = "logout.php";// if not found back to logout interface
        break;
        case 'illegal' ://for illegal
          alert('You did not take this class! Logging you out for security');
          window.location = "logout.php";
        break;
        case 'failure' :// facing quarrel issue also back to the logout
          alert('We are facing some issues! Logging you out for security');
          window.location = "logout.php";
        break;
        case 'none' :
          $('.delete-warning').modal('hide');//here to check and delete warnings code, year, section and slow
            $('.class').each(function(k,v) {
              if($(this).find('.code').text() == code &&
                 $(this).find('.year').text() == year &&
                 $(this).find('.section').text() == section) {
                  $(this).hide('slow',function() {
                    $(this).remove();
                  });
              return;
              }
          });
        break;
      }
    }
  });
}

//function to ad the class from the input select
function addTheClass() {
  var fields = $('#add_class_form input,#add_class_form select');
  var data = {};
  for(i = 0;i < fields.length;i++) {
   data[jQuery(fields[i]).attr('name')] = jQuery(fields[i]).val();
  }
  console.log(data);

  //verify code
  if(!verify('code',data.code)) {
        alert('Invalid Code!');
        $('input[name=code]').val('');
        return;
      }

      //verify year
  if(!verify('number',data.year)) {
    alert('Invalid Year!');
    $('input[name=year]').val('');
    return;
  }

  //verify year
  if(!verify('number',data.section)) {
    alert('Invalid Section!');
    $('input[name=section]').val('');
    return;
  }

  //verify semester
  if(!verify('number',data.semester)) {
    alert('Invalid Semester!');
    $('input[name=semester]').val('');
    return;
  }

  //roll ID number start
  if(!verify('roll',data.start)) {
    alert('Invalid Starting/Ending Roll Number!');
    $('input[name=end],input[name=start]').val('');
    return;
  }

  //roll ID number end
  if(!verify('roll',data.end)) {
    alert('Invalid Starting/Ending Roll Number!');
    $('input[name=end],input[name=start]').val('');
    return;
  }

  //check the input if parceIntValue
  $.ajax({
    url : 'php/add_class.php',
    type : 'post',
    data : data,
    dataType : 'json',
    success : function (r) {
      if(r.error == 'code') {
        alert('Invalid Code!');
        $('input[name=code]').val('');
        return;
      }
      if(r.error == 'year') {
        alert('Invalid Year!');
        $('input[name=year]').val('');
        return;
      }
      if(r.error == 'section') {
        alert('Invalid Section!');
        $('input[name=section]').val('');
        return;
      }
      if(r.error == 'semester') {
        alert('Invalid Semester!');
        $('input[name=semester]').val('');
        return;
      }
      if(r.error == 'roll') {
        alert('Invalid Starting/Ending Roll Number!');
        $('input[name=end],input[name=start]').val('');
        return;
      }
      if(r.error == 'exists') {
        alert('This class is already added!');
        $('input[name=code]').val('');
        return;
      }
      $('.wrapper').prepend('<div class="class"> <a class="no-decoration" href="take.php?cN='+r.class_id+'"> <div><strong>Code</strong> : <span class="code">'+r.code+'</span></div> <div><strong>Section</strong> : <span class="section">'+r.section+'</span></div> <div><strong>Year</strong> : <span class="year">'+r.year+'</span> </div> <div><strong>Classes</strong> : 0</div> </div></a>');
      $('.wrapper .class').first().hide();
      $('.wrapper .class').first().show('slow');
      $('.modal').modal('hide');
      $('.no-classes').remove();
    },
    error : function (err) {
      console.log(err);
    }
  });
}