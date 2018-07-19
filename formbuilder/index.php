<?php

require "assets/includes/init.php";

$contactForm = new FormBuilder($config);
$thankYou = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
	try{
		$contactForm->validate();
		$contactForm->send();
		$thankYou = $config['thankYouMessage'];
		
		if(IS_AJAX){
			echo json_encode(array('success'=>1));
			exit;
		}
	}
	catch(FormValidateException $e){
		if(IS_AJAX){
			echo json_encode($e->errors);
			exit;
		}
		else{
			$contactForm->populateValuesFromArray($_POST);
		}
	}
	catch(Exception $e){
		die('{"exception":"'.$e->getMessage().'"}');
	}
	
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo $_GET['embed']? 'class="embed"':'' ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $config['headerText']?></title>

<link rel="stylesheet" type="text/css" href="assets/fonts/quicksand.css" />
<link rel="stylesheet" type="text/css" href="assets/themes/<?php echo $config['colorTheme']?>/css/styles.css" />

<script type="text/javascript" src="assets/js/jquery.js"></script>
<script type="text/javascript" src="assets/js/forms.js"></script>

</head>

<body>

<div id="formContainer">

	<?php
	
	if($thankYou){
		echo $thankYou;
	}
	else{
		echo '<h1>'.$config['headerText'].'</h1>';
		$contactForm->build();
	}
	
	?>
  
</div>
   
<div id="thankYou">
    <?php echo $config['thankYouMessage']?>
</div>

</body>
</html>