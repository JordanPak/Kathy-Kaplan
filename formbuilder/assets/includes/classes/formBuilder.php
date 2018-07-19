<?php

class FormBuilder{
	private $config = array();
	public $patterns = array(
		'url' 		=> '/(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?/i',
		'phone'		=> '#^(?:(?:[\+]?(?<CountryCode>[\d]{1,3}(?:[ ]+|[\-.])))?[(]?(?<AreaCode>[\d]{3})[\-/)]?(?:[ ]+)?)?(?<Number>[a-zA-Z2-9][a-zA-Z0-9 \-.]{6,})(?:(?:[ ]+|[xX]|(i:ext[\.]?)){1,2}(?<Ext>[\d]{1,5}))?$#',
		'zip'		=> '/(^\d{5}$)|(^\d{5}-\d{4}$)/',
		'date'		=> '#^(((((((0?[13578])|(1[02]))[\.\-/]?((0?[1-9])|([12]\d)|(3[01])))|(((0?[469])|(11))[\.\-/]?((0?[1-9])|([12]\d)|(30)))|((0?2)[\.\-/]?((0?[1-9])|(1\d)|(2[0-8]))))[\.\-/]?(((19)|(20))?([\d][\d]))))|((0?2)[\.\-/]?(29)[\.\-/]?(((19)|(20))?(([02468][048])|([13579][26])))))$#',
		'time'		=> '/^((([0]?[1-9]|1[0-2])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?( )?(AM|am|aM|Am|PM|pm|pM|Pm))|(([0]?[0-9]|1[0-9]|2[0-3])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?))$/',
		'number'	=> '/^[\d\.,]+$/'
	);
	
	public function __construct($config){
		$this->config = $config;
		
		if($this->config['captcha']){
			
			array_push($this->config['fields'],array(
				'type'		=> 'textField',
				'validation'=> 'captcha',
				'required'	=> true,
				'errorText'	=> 'Your captcha result is wrong.'
			));
		}
	}
	
	public function validate(){
		$errors = array();
		$fields = &$this->config['fields'];

		for($i=0;$i<count($fields);$i++){
			
			$field = &$fields[$i];
			
			if($field['validation']){
				$field['validation'] = str_replace(
					array_keys($this->patterns),
					array_values($this->patterns),
					$field['validation']
				);
				
				if(!in_array($field['validation'],array('email','captcha')) && !preg_match('/^(\W).+\1[a-z]*$/',trim($field['validation']))){
					$field['validation'] = '/^'.$field['validation'].'$/i';
				}
			}
			
			$requiredEmtpy	= ($field['required'] && mb_strlen($_POST['field'.$i],'utf-8')<1);
			$selectDefault	= ($field['required'] && $field['type'] == 'select' && $_POST['field'.$i] == $field['default']);
			$emailInvalid	= ($field['validation'] == 'email' && !PHPMailer::ValidateAddress($_POST['field'.$i]));
			$captchaWrong	= ($field['validation'] == 'captcha' && $_POST['field'.$i] != $_SESSION['captchaResult']);
			$regexWrong		= ($field['validation'] && !in_array($field['validation'],array('email','captcha')) && ($field['required'] || mb_strlen($_POST['field'.$i],'utf-8') > 0) && @!preg_match($field['validation'],$_POST['field'.$i]));
			
			if(	$requiredEmtpy || $selectDefault || $emailInvalid || $captchaWrong || $regexWrong ){
				$errors['field'.$i] = $field['errorText'] ? $field['errorText'] : 'You\'ve entered incorrect data.';
				$field['displayError'] = true;
			}
		}
		
		if(	$_SESSION['lastSubmit'] && ( time() - $_SESSION['lastSubmit'] < 30 || $_SESSION['submitsLastHour'][date('d-m-Y-H')] > 10 )){
			$errors['general'] = 'Please wait for a few minutes before submitting again.';
		}
		
		if(count($errors)){
			throw new FormValidateException($errors);
		}
	}
	
	public function populateValuesFromArray($arr){
		$fields = &$this->config['fields'];
				
		for($i=0;$i<count($fields);$i++){
			$fields[$i]['value'] = $arr['field'.$i];						
		}
	}
	
	public function send(){
		$fields = &$this->config['fields'];
				
		$_SESSION['lastSubmit'] = time();
		$_SESSION['submitsLastHour'][date('d-m-Y-H')]++;
		
		$mail = new PHPMailer(true);
		$mail->CharSet = 'utf-8';

		if($this->config['smtp']){
			$mail->IsSMTP();

			$mail->SMTPAuth   = true;
			$mail->Host       = $this->config['smtp']['host'];
			$mail->Port       = $this->config['smtp']['port'];
			$mail->Username   = $this->config['smtp']['username'];
			$mail->Password   = $this->config['smtp']['password'];
		}
		else{
			$mail->IsMail();
		}
		
		$msg = '<font face="Lucida Grande,Corbel,Arial,sans-serif" color="#565656"><table border=0 cellpadding="4" cellspacing="5" width="500">
					<tr>
						<td colspan="2" bgcolor="#eeeeee" valign="middle" align="center"><h2 style="margin:0;padding:8px;color:#888888;">'.$this->config['headerText'].' Submission</h2></td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>';
		
		for($i=0;$i<count($fields);$i++){

			if($fields[$i]['validation'] == 'captcha' || ($fields[$i]['type'] == 'select' && $fields[$i]['default'] == $_POST['field'.$i])){
				continue;
			}

			if(isset($_POST['field'.$i]) && mb_strlen(trim($_POST['field'.$i]),"utf-8") > 0){
				
				$_POST['field'.$i] = htmlspecialchars($_POST['field'.$i]);
				
				$msg.= '<tr><td valign="top" bgcolor="#eeeeee"><small>'.$fields[$i]['label'].':&nbsp;&nbsp;&nbsp;</small></td><td>';
				
				if($fields[$i]['type'] == 'textArea'){
					$msg.=nl2br($_POST['field'.$i]);
				}
				else if($fields[$i]['type'] == 'checkBox'){
					$msg.='Yes';
				}
				else if($fields[$i]['items']){
					$msg.= $fields[$i]['items'][$_POST['field'.$i]];
				}
				else $msg.= $_POST['field'.$i];
				
				$msg.='</td></tr>';
			}
		}
		
		$msg .= '</table></font>';
		
		foreach($this->config['recipient'] as $name => $email){
			$mail->AddAddress($email,$name);
		}
		
		$name = $_POST['field'.$this->searchFieldIndex('fromName')];
		$email = $_POST['field'.$this->searchFieldIndex('fromEmail')];
		
		$mail->Subject = $_SERVER['HTTP_HOST'].' Form Submission';
		$mail->MsgHTML($msg);
		
		$mail->AddReplyTo($email,$name);
		$mail->SetFrom($email,$name);
		
		if($this->config['forceSender']){
			$mail->Sender = $this->config['forceSender'];
		}
		else $mail->Sender = 'form@'.$_SERVER['HTTP_HOST'];
		
		try {
			$mail->Send();
		}
		catch(Exception $e){
			
			// Fallback version.
			
			foreach($this->config['recipient'] as $receiverName=>$receiverEmail){

				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
				$headers .= 'To: '.$receiverEmail. "\r\n";
				$headers .= 'From: '.$email . "\r\n";
		
				$result = mail($receiverEmail, $mail->Subject, $msg, $headers);
				
				if(!$result){
					throw new Exception("Mail function exception",1);
				}
			}
		}
	}
	
	public function build(){
		
		?>
		<form method="post" action="" class="formGen">
    	
		<?php
        
            foreach ($this->config['fields'] as $fID=>$field){
                
                $fieldID = 'field'.$fID;
                
				if($field['validation'] == 'captcha'){
					$num1 = rand(1,10);
					$num2 = rand(1,10);
					
					$_SESSION['captchaResult'] = $num1+$num2;
					$field['label'] = $num1.' + '.$num2.' =';
				}
				
                ?>
                
                <div class="formRow">
                
                <label for="<?php echo $fieldID ?>">
                    <?php   
                        echo $field['label'];
                        
						if($field['validation']!='captcha'){
						
							if($field['required']){
								echo'<span class="star">*</span>';
							}
							
							echo ':';
						}
						
                    ?>
                </label>
                <?php
				
                $class = $field['type'];
                $value = '';
                
                if($field['required']){
                    $class.= ' required';
                }
                
				if($field['skipTransform']){
					$class.= ' skip';
				}
				
                if($field['value']){
                    $value = ' value="'.htmlspecialchars($field['value']).'"';
                }
                
                if($field['validation']){
                    $class.=' '.$field['validation'];
                }
                
                $class = 'class="'.$class.'"';
                
                switch($field['type']){
                    case 'textField':
                        ?>
                        <input type="text" name="<?php echo $fieldID ?>" id="<?php echo $fieldID ?>" <?php echo $class,$value?> />
                        <?php
					break;
                    case 'textArea':
                        ?>
                        <textarea name="<?php echo $fieldID ?>" id="<?php echo $fieldID ?>" rows="5" cols="40" <?php echo $class,$errorText?>><?php echo $field['value']?></textarea>
                        <?php
					break;
                    case 'checkBox':
						if($field['value']){
							$value = ' checked="checked"';
						}
                        ?>
                        <input type="checkbox" name="<?php echo $fieldID ?>" id="<?php echo $fieldID ?>" <?php echo $class,$value?> />
                        <?php
					break;
                    case 'radio':
                        foreach ($field['items'] as $k=>$v){
                            $radioID = $fieldID.'_'.$k;
                            
                            $checked = '';
                            if(isset($field['value']) && $field['value'] == $k){
                                $checked = ' checked="checked"';
                            }
                            
                            ?>
                                <input type="radio" name="<?php echo $fieldID?>" id="<?php echo $radioID?>" <?php echo $class, $checked?> value="<?php echo $k?>" />
                                <label for="<?php echo $radioID?>" class="radioLabel"><?php echo $v?></label>
                            <?php									
                        }
					break;
					case 'select':
					
						?>
                        
                        <select name="<?php echo $fieldID?>" id="<?php echo $fieldID?>" <?php echo $class,$errorText?>>
                        
                        <?php
					
						foreach ($field['items'] as $k=>$v){
							
                            $selected = '';
                            if(isset($field['value']) && $field['value'] == $k){
                                $selected = ' selected="selected"';
                            }
                            
                            ?>
                            	<option <?php echo $selected?> value="<?php echo $k?>"><?php echo $v?></option>
                            <?php									
                        }
						
						?>
                        
                        </select>
                        
                        <?php
					break;
                }

                if($field['displayError']){
                    echo '<div class="staticError">'.$field['errorText'].'</div>';
                }
                
                ?>
                
                </div>
                
                <?php
            }
        
        ?>
        <div class="formRow">
           <input type="submit" value="Submit Form" id="submit" />
        </div>
		</form>

		<?php
	}


	private function searchFieldIndex($var){
		$fields = &$this->config['fields'];
		
		for($i=0;$i<count($fields);$i++){
			if($fields[$i][$var]){
				return $i;
			}
		}
	}

}


class FormValidateException extends Exception{
	public $errors = array();
	public function __construct($arr){
		parent::__construct('Errors Encountered');
		$this->errors = $arr;
	}
}
?>

	