<?php
//Check if init.php exists
 require_once(__DIR__ .'/../core/frontinit.php');
 
//Start new Admin Object
$admin = new Admin();

//Check if Admin is logged in
if (!$admin->isLoggedIn()) {
  Redirect::to($web->url.'admin/login');	
}

//Getting freelancer Data
$userid = $_GET['id'];
$qs = DB::getInstance()->get("user", "*", ["userid" => $userid, "LIMIT" => 1]);
if ($qs->count() === 1) {
 foreach($qs->results() as $rs) {
 }
}else {
  Redirect::to($web->url.'admin/userlist');
}	

//Edit Profile Data
if(isset($_POST['details'])){
if (Input::exists()) {
 if(Token::check(Input::get('token'))){

	$errorHandler = new ErrorHandler;
	
	$validator = new Validator($errorHandler);
	
	$validation = $validator->check($_POST, [
	  'name' => [
		 'required' => true,
		 'minlength' => 2,
		 'maxlength' => 200
	   ],
	  'email' => [
	     'required' => true,
	     'email' => true,
	     'maxlength' => 100,
	     'minlength' => 2
	  ],		 
	  'username' => [
	     'required' => true,
	     'maxlength' => 100,
	     'minlength' => 3,
	     'alnum' => true
	  ],
	   'organizationid' => [
	     'required' => true
	   ],
	   'positionid' => [
	     'required' => true
	   ]
	]);
		 
    if (!$validation->fails()) {
		
		//Update
		$Update = DB::getInstance()->update('user',[
		   'username' => Input::get('username'),
		   'name' => Input::get('name'),
           'email' => Input::get('email'),
		   'organizationid' => Input::get('organizationid'),
           'positionid' => Input::get('positionid')
		],[
		    'userid' => $rs->userid
		  ]);
		
	   if (count($Update) > 0) {
			$updatedError = true;
		} else {
			$hasError = true;
		}
		
			
	 } else {
     $error = '';
     foreach ($validation->errors()->all() as $err) {
     	$str = implode(" ",$err);
     	$error .= '
	           <div class="alert alert-danger fade in">
	            <a href="#" class="close" data-dismiss="alert">&times;</a>
	            <strong>Error!</strong> '.$str.'
		       </div>
		       ';
     }
   }

  }
 }
}

/*Edit Image Data*/
if(isset($_POST['picture'])){
if (Input::exists()) {
  if (Token::check(Input::get('token'))) {
  	
	$path = 'uploads/';
	$path_new = 'source/nominee/uploads/';

    $valid_formats = array("jpg", "png", "gif", "bmp");
   
    $name = $_FILES['photoimg']['name'];
    $size = $_FILES['photoimg']['size'];

    if(strlen($name))
	{
	  list($txt, $ext) = explode(".", $name);
      if(in_array($ext,$valid_formats) && $size<(1400*1400))
	   {
	     $actual_image_name = time().substr($txt, 5).".".$ext;
		 $image_name = $actual_image_name;
		 $newname=$path.$image_name;
         $tmp = $_FILES['photoimg']['tmp_name'];
         if(move_uploaded_file($tmp, $path_new.$actual_image_name))
	      {
	      	
	       if ($rs->imagelocation !== 'uploads/default.png') {
				unlink('source/nominee/'.$rs->imagelocation);
				
				$Update = DB::getInstance()->update('user',[
				    'imagelocation' => $newname
				],[
				    'userid' => $rs->userid
				]);
				
			   if (count($Update) > 0) {
					$updatedError = true;
				} else {
					$hasError = true;
				}	 
		   } else {
				
				$Update = DB::getInstance()->update('user',[
				    'imagelocation' => $newname
				],[
				    'userid' => $rs->userid
				]);
				
			   if (count($Update) > 0) {
					$updatedError = true;
				} else {
					$hasError = true;
				}	
		   }	      	
			  
					
	      }else{
		   $imageError = true;	
     	  }
       }else{
       	  $formatError = true;				
	   }
      }else{
      	  $selectError = true;
      }	
  	
  }
 }	
}

/*Edit Password Data*/
if(isset($_POST['register'])){
if (Input::exists()) {
  if (Token::check(Input::get('token'))) {
 
 	$errorHandler = new ErrorHandler;
	
	$validator = new Validator($errorHandler);
	
	$validation = $validator->check($_POST, [
	  'password_current' => [
	     'required' => true,
	     'maxlength' => 300
	  ],
	   'password' => [
	     'required' => true,
	     'minlength' => 6
	   ],
	   'confirmPassword' => [
	     'required' => true,
	     'match' => 'password'
	   ]
	]);
		 
    if (!$validation->fails()) {
  	
		if (Hash::make(Input::get('password_current'), $rs->salt) !== $rs->password) {
			$passError = true;
		} else {
		  $salt = Hash::salt(32);			
		  
		  $Update = DB::getInstance()->update('user',[
		   'password' => Hash::make(Input::get('password'), $salt),
		   'salt' => $salt
			],[
			    'userid' => $rs->userid
			]);
			
		   if (count($Update) > 0) {
				$updatedError = true;
			} else {
				$hasError = true;
			}
		 
		}
      
	 } else {
     $error = '';
     foreach ($validation->errors()->all() as $err) {
     	$str = implode(" ",$err);
     	$error .= '
	           <div class="alert alert-danger fade in">
	            <a href="#" class="close" data-dismiss="alert">&times;</a>
	            <strong>Error!</strong> '.$str.'
		       </div>
		       ';
     }
   }	
  
    
 }
}
}
?>
<!DOCTYPE html>
<html lang="en-US" class="no-js">
	
    <!-- Include header.php. Contains header content. -->
    <?php include ('template/header.php'); ?> 

 <body class="skin-green sidebar-mini">
     
     <!-- ==============================================
     Wrapper Section
     =============================================== -->
	 <div class="wrapper">
	 	
        <!-- Include navigation.php. Contains navigation content. -->
	 	<?php include ('template/navigation.php'); ?> 
        <!-- Include sidenav.php. Contains sidebar content. -->
	 	<?php include ('template/sidenav.php'); ?> 
	 	
	  <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1><?php echo $lang->nominee; ?><small><?php echo $lang->section; ?></small></h1>
          <ol class="breadcrumb">
            <li><a href="<?php echo $web->url; ?>admin/dashboard"><i class="fa fa-dashboard"></i> <?php echo $lang->home; ?></a></li>
            <li class="active"><?php echo $lang->edit; ?> <?php echo $lang->nominee; ?></li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">	 	
 
		 <div class="row">	
		 	
		 <div class="col-lg-12">
      	
		 <?php if(isset($selectError)) { //If errors are found ?>
	       <div class="alert alert-danger fade in">
	        <a href="#" class="close" data-dismiss="alert">&times;</a>
	        <strong>Error!</strong> Please select image..! Please try again.
		   </div>
	      <?php } ?>
	      
		 <?php if(isset($formatError)) { //If errors are found ?>
	       <div class="alert alert-danger fade in">
	        <a href="#" class="close" data-dismiss="alert">&times;</a>
	        <strong>Error!</strong> Invalid file formats..! Please try again.
		   </div>
	      <?php } ?>
	      
		 <?php if(isset($formatprofileError)) { //If errors are found ?>
	       <div class="alert alert-danger fade in">
	        <a href="#" class="close" data-dismiss="alert">&times;</a>
	        <strong>Error!</strong> Invalid file formats..! Please try again. Formats accepted are jpg,png,gif,bmp and size should be 1400 * 1400.
		   </div>
	      <?php } ?>
	      
		 <?php if(isset($imageError)) { //If errors are found ?>
	       <div class="alert alert-danger fade in">
	        <a href="#" class="close" data-dismiss="alert">&times;</a>
	        <strong>Error!</strong> Failed to upload Image. Please try again.
		   </div>
	      <?php } ?>
		 	
		  <?php if (isset($error)) {
			  echo $error;
		  } ?>
	        
	      <?php if(isset($hasError)) { //If errors are found ?>
	       <div class="alert alert-danger fade in">
	        <a href="#" class="close" data-dismiss="alert">&times;</a>
	        <strong>Error!</strong> Your current password is wrong.
		   </div>
	      <?php } ?>
	
		  <?php if(isset($noImageError) && $noImageError == true) { //If email is sent ?>
		   <div class="alert alert-success fade in">
		   <a href="#" class="close" data-dismiss="alert">&times;</a>
		   <strong>Success!</strong> You have successfully changed your Profile Image.</strong>.
		   </div>
		  <?php } ?>
	        
	      <?php if(isset($passError)) { //If errors are found ?>
	       <div class="alert alert-danger fade in">
	        <a href="#" class="close" data-dismiss="alert">&times;</a>
	        <strong>Error!</strong> Your current password is wrong
		   </div>
	      <?php } ?>
	

	      <?php if(isset($hasError)) { //If errors are found ?>
	       <div class="alert alert-danger fade in">
	        <a href="#" class="close" data-dismiss="alert">&times;</a>
	        <strong>Error!</strong> Please check if you've filled all the fields with valid information and try again. Thank you.
		   </div>
	      <?php } ?>
	
		  <?php if(isset($updatedError) && $updatedError == true) { //If email is sent ?>
		   <div class="alert alert-success fade in">
		   <a href="#" class="close" data-dismiss="alert">&times;</a>
		   <strong>Success!</strong> The details have been successfully Updated.</strong>
		   </div>
		  <?php } ?>
		  
          </div>	
           
          <div class="col-lg-4">
          	<?php $selected = (Input::get('m') == 'adminnomineeprofile') ? ' active' : ''; ?>
          	<?php $image = (Input::get('m') == 'adminnomineeimage') ? ' active' : ''; ?>
          	<?php $active = (Input::get('m') == 'adminnomineepassword') ? ' active' : ''; ?>
	         <div class="list-group">
	         <a href="<?php echo $web->url; ?>admin/editnominee/profile/<?php echo $userid ?>" class="list-group-item<?php echo $selected; ?>">
	          <em class="fa fa-fw fa-user-md text-white"></em>&nbsp;&nbsp;&nbsp;<?php echo $lang->nominee; ?> <?php echo $lang->details_details; ?>
			 </a>
	         <a href="<?php echo $web->url; ?>admin/editnominee/image/<?php echo $userid ?>" class="list-group-item<?php echo $image; ?>">
	          <em class="fa fa-fw fa-image text-white"></em>&nbsp;&nbsp;&nbsp;<?php echo $lang->nominee; ?> <?php echo $lang->image; ?>
			 </a>
	         <a href="<?php echo $web->url; ?>admin/editnominee/password/<?php echo $userid ?>" class="list-group-item<?php echo $active; ?>">
	          <em class="fa fa-fw fa-lock text-white"></em>&nbsp;&nbsp;&nbsp;<?php echo $lang->nominee; ?> <?php echo $lang->password; ?>
			 </a>
			 
	         </div>
		  
		  		 <div class="box box-info">
                <div class="box-header">
                  <h3 class="box-title"><?php echo $lang->nominated; ?> <?php echo $lang->for_for; ?>: -</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
					 <?php
					  $qo = DB::getInstance()->get("organization", "*", ["organizationid" => $rs->organizationid]);
						if ($qo->count()) {
							 foreach ($qo->results() as $ro) {
						     }
						}
					  $qp = DB::getInstance()->get("position", "*", ["positionid" => $rs->positionid]);
						if ($qp->count()) {
							 foreach ($qp->results() as $rp) {
						     }
						}
					 ?>	
				    <label><?php echo $lang->organization; ?></label><span class="help-block"> :- <?php echo $ro->name; ?></span><br/>
				    <label><?php echo $lang->position; ?></label><span class="help-block"> :- <?php echo $rp->name; ?></span>
				  
                  </div><!-- /.box-body -->
              </div><!-- /.box -->	
          </div>
          
		 <div class="col-lg-8">
		 <?php if (Input::get('m') == 'adminnomineeprofile') : ?>
		 <!-- Input addon -->
              <div class="box box-info">
                <div class="box-header">
                  <h3 class="box-title"><?php echo $lang->edit; ?> <?php echo $lang->nominee; ?> <?php echo $lang->details_details; ?></h3>
                </div>
                <div class="box-body">
                 <form role="form" method="post" id="editform"> 
                  <div class="form-group">	
                   <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-info"></i></span>
                    <input type="text" name="name" class="form-control" value="<?php 
					  if (isset($_POST['details'])) {
						 echo escape(Input::get('name')); 
					  } else {
					  echo escape($rs->name); 
					  } ?>" />
                   </div>
                    <p class="help-block"><?php echo $lang->nominee; ?> <?php echo $lang->name; ?></p>
                  </div>
                  <div class="form-group">	
                   <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-mail-reply"></i></span>
                    <input type="text" name="email" class="form-control" value="<?php 
					  if (isset($_POST['details'])) {
						 echo escape(Input::get('email')); 
					  } else {
					  echo escape($rs->email); 
					  } ?>"/>
                   </div>
                    <p class="help-block"><?php echo $lang->nominee; ?> <?php echo $lang->email; ?></p>
                  </div>
                  <div class="form-group">	
                   <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" name="username" class="form-control" value="<?php 
					  if (isset($_POST['details'])) {
						 echo escape(Input::get('username')); 
					  } else {
					  echo escape($rs->username); 
					  } ?>"/>
                   </div>
                    <p class="help-block"><?php echo $lang->nominee; ?> <?php echo $lang->username; ?></p>
                  </div>  
                                    
                  <div class="form-group">	
				    <label><?php echo $lang->organization; ?></label>
                   <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil-square"></i></span>
					<select name="organizationid" type="text" class="form-control" onclick="ajaxfunction(this.value)">
					 <?php
						   $categoryname = '';
					  echo $categoryname .= '<option value = "0">Nothing Selected</option>';
					  unset($categoryname); 
					  $query = DB::getInstance()->get("organization", "*", []);
						if ($query->count()) {
						   $x = 1;
							 foreach ($query->results() as $row) {

	                         if (isset($_POST['details'])) {
	                         	$selected = (Input::get('organizationid') === $rs->organizationid) ? ' selected="selected"' : '';
							  } else {
							  	$selected = ($row->organizationid === $rs->organizationid) ? ' selected="selected"' : '';
							  }
							  
							  echo $categoryname .= '<option value = "' . $row->organizationid . '" '.$selected.'>' . $row->name . '</option>';
							  unset($categoryname); 
							  $x++;
						     }
						}
					 ?>	
					</select>
                   </div>
                  </div>
                                    
                  <div class="form-group">	
				    <label><?php echo $lang->position; ?></label>
                   <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-pencil-square"></i></span>
					<select name="positionid" type="text" class="form-control" id="sub">
					</select>
                   </div>
                  </div>    
                                   
                  <div class="box-footer">
                    <input type="hidden" name="token" value="<?php echo Token::generate(); ?>" />
                    <button type="submit" name="details" class="btn btn-primary full-width"><?php echo $lang->submit; ?></button>
                  </div>
                 </form> 
                </div><!-- /.box-body -->
              </div><!-- /.box -->
              
		 <?php elseif (Input::get('m') == 'adminnomineeimage') : ?>
		  
		  		 <div class="box box-info">
                <div class="box-header">
                  <h3 class="box-title"><?php echo $lang->profile; ?> <?php echo $lang->picture; ?></h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form role="form" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="imagelocation" class="form-control" value="<?php echo escape($freelancer_imagelocation); ?>"/>
                  <div class="box-body">
                    <div class="form-group">
					 <div class="image text-center">
					  <img src="<?php 
					  if (isset($_POST['picture'])) {
						 echo $web->url; ?>source/nominee/<?php echo escape($newname); 
					  } else {
					  echo $web->url; ?>source/nominee/<?php echo escape($rs->imagelocation); 
					  } ?>" class="img-thumbnail" width="215" height="215"/>
					 </div>
                    </div>
                   <div style="position:relative;">
	                <a class='btn btn-primary' href='javascript:;'>
		            <?php echo $lang->choose; ?> <?php echo $lang->image; ?>...
		            <input type="file" name="photoimg" id="photoimg" style='position:absolute;z-index:2;top:0;left:0;filter: alpha(opacity=0);-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";opacity:0;background-color:transparent;color:transparent;' name="file_source" size="40"  onchange='$("#upload-file-info").html($(this).val());'>
	                <input type="hidden" name="image_name" id="image_name"/>
	                </a>
	                &nbsp;
	                <span class='label label-info' id="upload-file-info"></span>
                  </div>
				  
                  </div><!-- /.box-body -->
                  
                  <div class="box-footer">
                    <input type="hidden" name="token" value="<?php echo Token::generate(); ?>" />
                    <button type="submit" name="picture" class="btn btn-primary full-width"><?php echo $lang->submit; ?></button><br></br>
                  </div>

                </form>
              </div><!-- /.box -->	

		 <?php elseif (Input::get('m') == 'adminnomineepassword') : ?>	 
			 
		 <!-- Input addon -->
              <div class="box box-info">
                <div class="box-header">
                  <h3 class="box-title"><?php echo $lang->edit; ?> <?php echo $lang->password; ?></h3>
                </div>
                <div class="box-body">
                 <form role="form" method="post" id="editpassform"> 
                  <input type="hidden" name="nid" value="<?php echo escape($nid); ?>"/>
                  <div class="form-group">	
                   <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                    <input type="password" name="password_current" class="form-control" placeholder="<?php echo $lang->current; ?> <?php echo $lang->password; ?>"/>
                   </div>
                  </div>
                  <div class="form-group">	
                   <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="<?php echo $lang->new; ?> <?php echo $lang->password; ?>"/>
                   </div>
                  </div>
                  <div class="form-group">	
                   <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                    <input type="password" name="confirmPassword" class="form-control" placeholder="<?php echo $lang->confirm; ?> <?php echo $lang->new; ?> <?php echo $lang->password; ?>"/>
                   </div>
                  </div>
                                   
                  <div class="box-footer">
                    <input type="hidden" name="token" value="<?php echo Token::generate(); ?>" />
                    <button type="submit" name="register" class="btn btn-primary full-width"><?php echo $lang->submit; ?></button>
                  </div>
                 </form> 
                </div><!-- /.box-body -->
              </div><!-- /.box -->              
			 
		 <?php endif; ?>
		 
		</div><!-- /.col -->
		
        
			 
	    </div><!-- /.row -->		  		  
	   </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
	 	
      <!-- Include footer.php. Contains footer content. -->	
	  <?php include 'template/footer.php'; ?>	
	 	
     </div><!-- /.wrapper -->   

     
     <!-- ==============================================
	 Scripts
	 =============================================== -->
	 
    <!-- jQuery 2.1.4 -->
    <script src="<?php echo $web->url; ?>source/assets/js/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.6 JS -->
    <script src="<?php echo $web->url; ?>source/assets/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo $web->url; ?>source/assets/js/app.min.js" type="text/javascript"></script> 
	<script type="text/javascript">
	    function ajaxfunction(parent,id)
	    {
	        $.ajax({
			    type: "POST",
	            url: "<?php echo $web->url; ?>source/admin/template/requests/process.php",
			    data: "parent="+parent, 
			    cache: false,
	            success: function(data) {
	                $("#sub").html(data);
	            }
	        });
	    }
	</script>  
     
	 <script>	
		/*============================================
		Change Language
		==============================================*/
	 
		function changelanguage(languageid) {
			// id = unique id of the message/comment
			// type = type of post: message/comment
	
			$.ajax({
				type: "POST",
				url: "<?php echo $web->url; ?>source/admin/template/requests/changelanguage.php",
				data: "languageid="+languageid, 
				cache: false,
				success: function(html) {
					window.location.reload();
				}
			});
		}		
	</script>   
 	 
 	 <?php echo $web->google_analytics; ?> 	

    
</body>
</html>
