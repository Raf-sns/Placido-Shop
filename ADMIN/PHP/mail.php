<?php
/**
 * PLACIDO-SHOP FRAMEWORK - BACKEND
 * Copyright © Raphaël Castello, 2019-2022
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
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

// PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// include PHPMailer
include_once ROOT.'/PHP/LIBS/PHPMailer/PHPMailer.php';
include_once ROOT.'/PHP/LIBS/PHPMailer/SMTP.php';
include_once ROOT.'/PHP/LIBS/PHPMailer/Exception.php';

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


    try {

        // SEND MAIL by PHP MAILER
        $mail = new PHPMailer();
        // debug - un-comment this follow
        // $mail->SMTPDebug = 2; // 1=Client commands, 2=Client commands and server responses
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP(); // Use SMTP protocol
        // Mailbox infos
        $mail->Host = self::MAILBOX_HOST; // Host who use the mailbox
        $mail->SMTPAuth = true; // Auth. SMTP
        $mail->Username = self::MAILBOX_ACCOUNT; // Mailbox ex. cc@myhost.com
        $mail->Password = self::MAILBOX_PASSW; // Mailbox password
        $mail->SMTPSecure = 'ssl'; // Accept SSL
        $mail->Port = self::MAILBOX_PORT;
        // Set the encryption mechanism to use:
        // - SMTPS (implicit TLS on port 465) or
        // - STARTTLS (explicit TLS on port 587)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        // Priority | null (default), 1 = High, 3 = Normal, 5 = low
        $mail->Priority = 1;

        // Customize sender
        $mail->setFrom( PUBLIC_NOTIFICATION_MAIL, HOST );
        // Clear
        $mail->clearAddresses();
        // Add recipient
        $mail->addAddress($to);
        // Reply address
        $mail->addReplyTo(PUBLIC_NOTIFICATION_MAIL);
        // Set email format to HTML or not
        $mail->isHTML(true);
        // Subject
        $mail->Subject = $subject;
        // Email Body in HTML
        $mail->Body = $message;

        // SEND
        if( !$mail->send() ){

           // render error if it is
           $tab = array('error' => 'E-Mail error : '.$mail->ErrorInfo );
           echo json_encode($tab, JSON_FORCE_OBJECT);
           exit;
        }

    }
    catch (Exception $e){

        echo $e->errorMessage();
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

      // if all ok return true send_mail_by_PHPMailer exit on errors
      return true;

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

      return true;

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


      // VERIFY token
			token::verify_token();

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

      $subject = trim(htmlspecialchars($_POST['subject'], ENT_NOQUOTES, 'UTF-8'));

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
