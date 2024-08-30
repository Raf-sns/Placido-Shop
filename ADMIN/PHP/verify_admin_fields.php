<?php

// EMPTY NAME
if( empty($_POST['name']) ){

		$tab = array('error' => tr::$TR['empty_name'] );
		echo json_encode($tab);
		exit;
}
// VERIFY  NAME
if( !empty($_POST['name']) ){

		$name = trim(htmlspecialchars($_POST['name']));

		// IF MAX LENGTH
		if( iconv_strlen($name) > 100 ){

				$tab = array('error' => tr::$TR['too_large_name'] );
				echo json_encode($tab);
				exit;
		}

}
// END NAME


// EMPTY E-MAIL
if( empty($_POST['mail']) ){

		$tab = array('error' => tr::$TR['empty_mail'] );
		echo json_encode($tab);
		exit;
}
// VERIFY  E-MAIL
if( !empty($_POST['mail']) ){

		$mail = trim(htmlspecialchars($_POST['mail']));

		// IF MAX LENGTH
		if( iconv_strlen($mail) > 100 ){

				$tab = array('error' => tr::$TR['too_large_mail'] );
				echo json_encode($tab);
				exit;
		}

		// IF BAD FORMAT
		if( filter_var($mail, FILTER_VALIDATE_EMAIL ) == false ){

				$tab = array('error' => tr::$TR['bad_mail'] );
				echo json_encode($tab);
				exit;
		}

}
// END E-MAIL

// VERIFY PASSWORD
// EMPTY PASS
if( trim($_POST['passw']) == "" || empty($_POST['passw']) ){

		$tab = array('error' => tr::$TR['password_required'] );
		echo json_encode($tab);
		exit;
}
else {

		$passw = trim(htmlspecialchars($_POST['passw']));

}
// END PASS

?>
