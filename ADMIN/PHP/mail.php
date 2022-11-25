<?php
/**
 * PlACIDO-SHOP FRAMEWORK - BACK OFFICE
 * Copyright © Raphaël Castello - 2019 - 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	mail.php
 *
 * mail::send_mail_by_PHPMailer();
 * mail::mail_confirm_treatment($mail, $ARR);
 * mail::send_bill_by_mail($mail, $ARR);
 * mail::vendor_send_mail();
 * mail::send_mail_alert();
 *
 */

  require ROOT.'/PHP/LIBS/PHPMailer/PHPMailer.php';
	require ROOT.'/PHP/LIBS/PHPMailer/SMTP.php';


class mail extends config {


  /**
   * mail::send_mail_by_PHPMailer();
   * send a mail by PHPMailer method
   * @Param $to -> mail to send
   * @Param $subject -> suject of mail
   * @Param $message -> html content with datas
   *  !! need -> classes/Exception.php - classes/PHPMailer.php - classes/SMTP.php
   *
   * @param  {str} $to        mail to send mail
   * @param  {str} $subject   subject of e-mail
   * @param  {str} $message   mail content in (x)html
   * @return {void}           send mail with PHPMailer library
   */
  public static function send_mail_by_PHPMailer($to, $subject, $message){

        // debug - un-comment this
        // $mail->SMTPDebug = 2;
        // error_reporting(E_STRICT | E_ALL);

        // SEND MAIL by PHP MAILER
        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP(); // Use SMTP protocol
        $mail->Host = self::MAILBOX_HOST; // Host who use the mailbox
        $mail->SMTPAuth = true; // Auth. SMTP
        $mail->Username = self::MAILBOX_ACCOUNT; // Mailbox ex. cc@myhost.com
        $mail->Password = self::MAILBOX_PASSW; // Mailbox password
        $mail->SMTPSecure = 'ssl'; // Accept SSL
        $mail->Port = self::MAILBOX_PORT;

        // Personnaliser l'envoyeur
        $mail->setFrom( PUBLIC_NOTIFICATION_MAIL, HOST );
        // Ajouter le destinataire
        $mail->clearAddresses();
        $mail->addAddress($to);
        // L'adresse de réponse
        $mail->addReplyTo(PUBLIC_NOTIFICATION_MAIL);
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');
        // $mail->addAttachment('/var/tmp/file.tar.gz'); // Ajouter un attachement
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');
        $mail->isHTML(true); // Paramétrer le format des emails en HTML ou non

        $mail->Subject = $subject;
        $mail->Body = $message;
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';


        // SEND
        if( !$mail->send() ){

           // render error if it is
           $tab = array('error' => 'Mailer Error: '.$mail->ErrorInfo );
           echo json_encode($tab, JSON_FORCE_OBJECT);
           exit;
        }

        return true;

  }
  /**
   * mail::send_mail_by_PHPMailer();
   */



  /**
   * mail::mail_confirm_treatment($mail, $ARR);
   *
   * @param  {str}   $mail    mail to send confirmation of command treatement
   * @param  {array} $ARR     array of datas for template
   * @return {type}           send a communication mail
   */
  public static function mail_confirm_treatment($mail, $ARR){


    // MUSTACHE FOR TEMPLATE
    $options =  array('extension' => '.html');
    // // template loader
    $m = new Mustache_Engine(array(
        'loader' => new Mustache_Loader_FilesystemLoader(dirname(__DIR__) . '/templates', $options)
    ));
    // ask template -> mail_confirm_traitement.html
    $templ = 'just_comm_mail';
    // loads template from `templates/$templ.html` and renders it with the ARRAY.
    $message = $m->render($templ, $ARR);

    // SEND MAIL by PHP MAILER - return true if no error
    mail::send_mail_by_PHPMailer($mail, $ARR['subject'], $message);

    return true; // if all ok return true send_mail exit on errors

  }
  /**
   * mail::mail_confirm_treatment($mail, $ARR);
   */



  /**
   * mail::send_bill_by_mail($mail, $ARR);
   *
   * @param  {str} $mail    description
   * @param  {array} $ARR   description
   * @return {type}         send bill by mail
   */
  public static function send_bill_by_mail($mail, $ARR){


      // MUSTACHE FOR TEMPLATE
      $options =  array('extension' => '.html');
      // template loader
      $m = new Mustache_Engine(array(
          'loader' => new Mustache_Loader_FilesystemLoader(dirname(__DIR__) . '/templates', $options)
      ));
      // ask template -> mail_confirm_traitement.html
      $templ = 'bill';
      // loads template from `templates/$templ.html` and renders it with the ARRAY.
      $message = $m->render($templ, $ARR);

      // SEND MAIL by PHP MAILER - return true if no error
      mail::send_mail_by_PHPMailer($mail, $ARR['subject'], $message);

      return true; // if all ok return true send_mail exit on errors

  }
  /**
   * mail::send_bill_by_mail($mail, $ARR);
   */



  /**
   * mail::vendor_send_mail();
   *
   * @return {type}  send mail communication
   */
  public static function vendor_send_mail(){


      // VERIFY TOKEN
      $token = trim(htmlspecialchars($_POST['token']));
      program::verify_token($token);

      // empty cases
      if( empty($_POST['subject']) ){

            $tab = array( 'error' => tr::$TR['empty_subject_mail'] );
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
      }

      if( empty($_POST['message']) ){

            $tab = array( 'error' => tr::$TR['empty_mess_mail'] );
            echo json_encode($tab, JSON_FORCE_OBJECT);
            exit;
      }

      // data treatment
      $mail = trim(htmlspecialchars($_POST['mail']));

      // bad mail
      if( filter_var($mail, FILTER_VALIDATE_EMAIL) == false ){

          echo json_encode( array( 'error' => tr::$TR['bad_mail'] ),
          JSON_FORCE_OBJECT);
          exit;
      }

      $subject = trim(htmlspecialchars($_POST['subject']));

		  $message = tools::parse_line_breaks(trim(htmlspecialchars($_POST['message'])));

			// date mail
      $Date_Now = new DateTime('now', new DateTimeZone(TIMEZONE) );
      $date_mail = tools::format_date_locale( $Date_Now, 'FULL' , 'NONE', null );

      // array for mustache
      $ARR = array(
				'subject' => $subject,
	      'message' => nl2br($message),
	      'shop_title' => WEBSITE_TITLE,
	      'shop_img' => LOGO,
	      'shop_mail' => PUBLIC_NOTIFICATION_MAIL,
	      'date' => ucfirst($date_mail),
	      'host' => HOST,
				'year' => date('Y'),
	      'lang' => LANG_FRONT, // here use lang front
	      'tr' => tr::$TR
			);

      // MUSTACHE FOR TEMPLATE
      $options =  array('extension' => '.html');
      // // template loader
      $m = new Mustache_Engine(array(
          'loader' => new Mustache_Loader_FilesystemLoader(dirname(__DIR__) . '/templates', $options)
      ));

      // ask template -> mail_confirm_traitement.html
      $templ = 'just_comm_mail';

      // loads template from `templates/$templ.html` and renders it with the ARRAY.
      $message = $m->render($templ, $ARR);

      // SEND MAIL by PHP MAILER - return true if no error
      if( mail::send_mail_by_PHPMailer($mail, $subject, $message) ){

          unset($_POST);

          $tab = array('success' => tr::$TR['success_send_message'] );
          echo json_encode($tab, JSON_FORCE_OBJECT);
          exit;

      }

  }
  /**
   * mail::vendor_send_mail();
   */



  /**
   * mail::send_mail_alert();
   *
   * @return {type}  send alert mail if too much connections
   */
  public static function send_mail_alert(){


      $subject = tr::$TR['security_alert'];

		  $message = tr::$TR['alert_connections'];

			// date mail
      $Date_Now = new DateTime('now', new DateTimeZone(TIMEZONE) );
      $date_mail = tools::format_date_locale( $Date_Now, 'FULL' , 'SHORT', null );

      // array for mustache
      $ARR = array(
				'subject' => $subject,
	      'message' => $message,
	      'shop_title' => WEBSITE_TITLE,
	      'shop_img' => LOGO,
	      'shop_mail' => false,
	      'date' => ucfirst($date_mail),
	      'host' => HOST,
				'year' => date('Y'),
	      'lang' => LANG_FRONT, // here use lang front
	      'tr' => tr::$TR
			);

      // MUSTACHE FOR TEMPLATE
      $options =  array('extension' => '.html');
      // // template loader
      $m = new Mustache_Engine(array(
          'loader' => new Mustache_Loader_FilesystemLoader(dirname(__DIR__) . '/templates', $options)
      ));

      // ask template -> mail_confirm_traitement.html
      $templ = 'just_comm_mail';

      // loads template from `templates/$templ.html` and renders it with the ARRAY.
      $message = $m->render($templ, $ARR);

      // SEND MAIL by PHP MAILER
      mail::send_mail_by_PHPMailer(PUBLIC_NOTIFICATION_MAIL, $subject, $message);

  }
  /**
   * mail::send_mail_alert();
   */

}
// END class mail::

?>
