<?php
/**
 * PLACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello, 2019-2024
 * Organisation: SNS - Web et informatique
 * Website / contact: https://sns.pm
 *
 * Script name:	mail.php
 *
 * CLASS mail::
 *
 * mail::send_mail_by_PHPMailer( $to, $subject, $message );
 * mail::send_order_to_customer( $SALE );
 * mail::send_notif_new_sale( $SALE );
 * mail::send_mail_contact();
 * mail::get_all_admins_mail();
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
   * mail::send_mail_by_PHPMailer( $to, $subject, $message );
   * send a mail by PHPMailer
   *
   * @param  {mixed}  $to        array / mail to send email
   * @param  {string} $subject   subject of email
   * @param  {string} $message   mail content in html
   * @return {void}           send mail with PHPMailer library
   *
   */
  public static function send_mail_by_PHPMailer( $to, $subject, $message ){


      // i. no try catch here : Nothing should derail a sale

      // SEND MAIL by PPHPMailer
      $mail = new PHPMailer();
      // debug - un-comment this follow
      // $mail->SMTPDebug = 2; // 1=Client commands, 2=Client commands and server responses

      $mail->CharSet = 'UTF-8';
			// Use SMTP
      $mail->isSMTP();
			$mail->SMTPAuth = true; // Auth. SMTP
			$mail->SMTPSecure = 'ssl'; // Accept SSL
			// Mailbox infos
      $mail->Host = self::MAILBOX_HOST; // Outgoing server
      $mail->Username = self::MAILBOX_ACCOUNT; // Mailbox email
      $mail->Password = self::MAILBOX_PASSW; // Mailbox password
      $mail->Port = self::MAILBOX_PORT; // Mailbox port

      // Set the encryption mechanism to use:
      // - SMTPS (implicit TLS on port 465) or
      // - STARTTLS (explicit TLS on port 587)
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

      // Priority | null (default), 1 = High, 3 = Normal, 5 = low
      $mail->Priority = 1;

      // Mail from -> public communication email + website title
      $mail->setFrom( PUBLIC_NOTIFICATION_MAIL, WEBSITE_TITLE );

      $mail->clearAddresses();

      // Mail to send
      if( is_array($to) ){

          // multiples emails
          foreach( $to as $admin ){
              $mail->addAddress( $admin['mail'], $admin['name'] );
          }
      }
      else{
          // one mail to send
          $mail->addAddress($to);
      }

      // Reply address
      $mail->addReplyTo( PUBLIC_NOTIFICATION_MAIL, WEBSITE_TITLE );
			// Mail in HTML or not
      $mail->isHTML(true);
			// Subject
      $mail->Subject = $subject;
			// Message in HTML
      $mail->Body = $message;

      // SEND
      if( !$mail->send() ){

          // if error : return false for a test case
          return false;
      }

      // return true for a test case
      return true;
  }
  /**
   * mail::send_mail_by_PHPMailer();
   */



  /**
   * mail::send_order_to_customer( $SALE );
   *
   * @return {html}  html template for send an order to customer
   */
  public static function send_order_to_customer( $SALE ){


    // DATES i.-> can remove delivery date &/ payment limit date
    // -> just comment
    // specify timezone un-bug in my server
    $timezone = new DateTimeZone(TIMEZONE);

    // date now for bill
    $date_now = new DateTime('now', $timezone);
    $date_now_format =
			tools::format_date_locale( $date_now, 'FULL' , 'SHORT', null );
		// captalize first letter
		$date_now_format = ucfirst($date_now_format);

    // DATE + 8 DAYS FOR LIVRAISON
    $date_liv = new DateTime('now', $timezone);
    $date_liv->add(new DateInterval('P8D'));
    $date_liv_format =
			tools::format_date_locale( $date_liv, 'SHORT' , 'NONE', null );

    // DATE + 14 DAYS FOR PAYMENT IF NOT PAYED
    $date_limit = new DateTime('now', $timezone);
    $date_limit->add(new DateInterval('P14D'));
    $date_limit_format =
			tools::format_date_locale( $date_limit, 'SHORT' , 'NONE', null );


    // ARRAY FOR TEMPLATE
    $ARR_order_mail = array(
      'logo' => LOGO,
      'title' => tr::$TR['your_order'],
      'host' => HOST,
      'shop_title' => WEBSITE_TITLE,
      'sale_id' => $SALE['sale_id'],
      'mail' =>       $SALE['customer_settings']['mail'],
      'firstname' =>  $SALE['customer_settings']['firstname'],
      'lastname' =>   $SALE['customer_settings']['lastname'],
      'address' =>    $SALE['customer_settings']['address'],
      'post_code' =>  $SALE['customer_settings']['post_code'],
      'city' =>       $SALE['customer_settings']['city'],
			'country' =>       $SALE['customer_settings']['country'],
      'firstname_sup' =>  $SALE['customer_settings']['firstname_sup'],
      'lastname_sup' =>   $SALE['customer_settings']['lastname_sup'],
      'address_sup' =>    ( $SALE['customer_settings']['address_sup'] == '' ) ? false : $SALE['customer_settings']['address_sup'],
      'post_code_sup' =>  $SALE['customer_settings']['post_code_sup'],
      'city_sup' =>       $SALE['customer_settings']['city_sup'],
			'country_sup' =>    $SALE['customer_settings']['country_sup'],
      'total_amount_sale_text' => $SALE['amount_text'],
      'total_tax_sale' => $SALE['total_tax_sale'],
      'date_now' => $date_now_format,
      'date_liv' => $date_liv_format,
      'date_limit' => $date_limit_format,
      'sale_render_url' => $SALE['sale_render_url'],
      'sale_render_url_title' => tr::$TR['sale_render_url_title'],
      'sale_render_see' => tr::$TR['sale_render_see'],
      'sold_products' => $SALE['products_settings'],
      'tr' => tr::$TR, // pass translation
      'lang' => LANG_FRONT,
      'year' => date('Y'),
      'for_admin' => false
    );
    // end  ARRAY FOR TEMPLATE


    // MUSTACHE FOR TEMPLATE

    // use .html instead of .mustache for default template extension
    $options =  array('extension' => '.html');
    // template loader
    $m = new Mustache_Engine(array(
        'loader' => new Mustache_Loader_FilesystemLoader(dirname(__DIR__) . '/templates/API', $options)
    ));
    // ask template -> home.html
    $templ = 'order';
    // loads template from `templates/$templ.html` and renders it.
    $message = $m->render($templ, $ARR_order_mail);

    // mail address to the customer to send BILL
    $to = $SALE['customer_settings']['mail'];

    // SUBJECT
	  $subject = WEBSITE_TITLE.' - '.tr::$TR['your_order'];

    // SEND MAIL by PHP MAILER
    mail::send_mail_by_PHPMailer($to, $subject, $message);

  }
  /**
   * mail::send_order_to_customer( $SALE );
   */



  /**
   * mail::send_notif_new_sale( $SALE );
   * @Param $SALE : 	sale
   * @return {void}   send notification mail new sale to all admins
   */
  public static function send_notif_new_sale( $SALE ){


      // send message new sale to vendor at the public notifications mail
      // get_all_admins_mail() -> return array( [mail], [name] )
      $to = mail::get_all_admins_mail();

      // DATE NOTIF
      // specify timezone
      $timezone = new DateTimeZone(TIMEZONE);

      // date now for bill
      $date_now = new DateTime('now', $timezone);
      $date_now_format =
				tools::format_date_locale( $date_now, 'FULL' , 'SHORT', null );


      // ARRAY FOR TEMPLATE
      $ARR_notif_mail = array(
        'logo' => LOGO,
        'title' => tr::$TR['new_sale_form_website'],
        'host' => HOST,
        'shop_title' => WEBSITE_TITLE,
        'sale_id' => $SALE['sale_id'],
        'mail' =>       $SALE['customer_settings']['mail'],
        'firstname' =>  $SALE['customer_settings']['firstname'],
        'lastname' =>   $SALE['customer_settings']['lastname'],
        'address' =>    $SALE['customer_settings']['address'],
        'post_code' =>  $SALE['customer_settings']['post_code'],
        'city' =>       $SALE['customer_settings']['city'],
				'country' =>    $SALE['customer_settings']['country'],
        'firstname_sup' =>  $SALE['customer_settings']['firstname_sup'],
        'lastname_sup' =>   $SALE['customer_settings']['lastname_sup'],
        'address_sup' =>    ( $SALE['customer_settings']['address_sup'] == '' ) ? false : $SALE['customer_settings']['address_sup'],
        'post_code_sup' =>  $SALE['customer_settings']['post_code_sup'],
        'city_sup' =>       $SALE['customer_settings']['city_sup'],
				'country_sup' =>    $SALE['customer_settings']['country_sup'],
        'total_amount_sale_text' => $SALE['amount_text'],
        'total_tax_sale' => $SALE['total_tax_sale'],
        'date_now' => $date_now_format,
        'date_liv' => false,
        'date_limit' => false,
        'sold_products' => $SALE['products_settings'],
        'tr' => tr::$TR, // pass translation
        'lang' => LANG_FRONT,
        'year' => date('Y'),
        'for_admin' => true
      );
      // end  ARRAY FOR TEMPLATE


      // MUSTACHE FOR TEMPLATE
      // ask template -> order.html
      $templ = 'order';

      // // use .html instead of .mustache for default template extension
      $options =  array('extension' => '.html');
      // // template loader
      $m = new Mustache_Engine(array(
          'loader' => new Mustache_Loader_FilesystemLoader(dirname(__DIR__) . '/templates/API', $options)
      ));

      // loads template from `templates/$templ.html` and renders it.
      $message = $m->render($templ, $ARR_notif_mail);

      // SUBJECT
  	  $subject = WEBSITE_TITLE.' - '.$ARR_notif_mail['title'];

      // SEND MAIL by PHP MAILER
      mail::send_mail_by_PHPMailer($to, $subject, $message);

  }
  /**
   * mail::send_notif_new_sale();
   */



  /**
   * mail::send_mail_contact();
   *
   * @return {type}  description
   */
  public static function send_mail_contact(){


    // start SESSION if not started
    if( session_status() == PHP_SESSION_NONE ){

        // start session
        session_start([
          'name' => 'PLACIDO-SHOP',
          'use_strict_mode' => true,
          'cookie_samesite' => 'Strict',
          'cookie_lifetime' => 3600, // 1 hour
          'gc_maxlifetime' => 3600,
          'cookie_secure' => true,
          'cookie_httponly' => true
        ]);
    }

    // put a session to not repost multiples emails
    if( !isset($_SESSION['nb_post_contact']) ){

        $_SESSION['nb_post_contact'] = 0;
    }
    else{

        $_SESSION['nb_post_contact']++;
    }

    // var_dump($_SESSION['nb_post_contact']);

    if( isset($_SESSION['nb_post_contact']) && $_SESSION['nb_post_contact'] >= 5 ){

        // ERROR
        $tab = array('error' => tr::$TR['quota_mail_exceeded'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

    // security
    $name = trim(htmlspecialchars($_POST['name'], ENT_NOQUOTES, 'UTF-8'));
    $mail = trim(htmlspecialchars($_POST['mail']));
    $mail = filter_var($mail, FILTER_SANITIZE_EMAIL);
    $message = trim(htmlspecialchars($_POST['message'], ENT_NOQUOTES, 'UTF-8'));

    // TEST EMPTY NAME
    if( empty($name) ){

        // ERROR
        $tab = array('error' => tr::$TR['empty_mess_name'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }
    // TEST NAME LENGTH
    if( strlen($name) > 100 ){

        // ERROR
        $tab = array('error' => tr::$TR['too_large_name'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

    // EMPTY MAIL
    if( empty($mail)  ){

        // ERROR
        $tab = array('error' => tr::$TR['empty_mail'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

    // test validity mail
    if( !filter_var($mail, FILTER_VALIDATE_EMAIL) ){

        // ERROR
        $tab = array('error' => tr::$TR['invalid_mail'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }
    // TEST MAIL LENGTH
    if( strlen($mail) > 100 ){

        // ERROR
        $tab = array('error' => tr::$TR['too_large_mail'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

    // EMPTY MESSAGE
    if( empty($message)  ){

        // ERROR
        $tab = array('error' => tr::$TR['empty_mess_mail'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }
    // TEST MESSAGE LEN
    if( strlen($message) > 2000 ){

        // ERROR
        $tab = array('error' => tr::$TR['too_large_message'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }

    // remove too much line breaks in the message
    $message = tools::parse_line_breaks($message);

    // DATE
    $timezone = new DateTimeZone(TIMEZONE);
    $date_now = new DateTime('now', $timezone);
		$date_db = $date_now->format('Y-m-d H:i:s');
    $date =
			tools::format_date_locale( $date_now, 'FULL' , 'SHORT', null );

    // SUBJECT
    $subject = HOST.' - '.tr::$TR['new_message_from_website'];

    // array for mustache
    $ARR_notif_mail =
    array(
					'logo' => LOGO,
          'title' => tr::$TR['new_message_from_website'],
          'website_title' => WEBSITE_TITLE,
          'host' => HOST,
          'date' => $date,
          'name' => $name,
          'mail' => $mail,
					'shop_mail' => PUBLIC_NOTIFICATION_MAIL,
          'message' => nl2br($message),
				 	'tr' => tr::$TR,
					'lang' => LANG_FRONT,
				);


    // MUSTACHE FOR TEMPLATE
    // ask template -> mail_confirm_traitement.html
    $templ = 'mail_contact';

    // use .html instead of .mustache for default template extension
    $options =  array('extension' => '.html');

    // template loader
    $m = new Mustache_Engine(array(
        'loader' => new Mustache_Loader_FilesystemLoader(dirname(__DIR__) . '/templates/API', $options)
    ));

    // loads template from `templates/$templ.html` and renders it with the ARRAY.
    $body = $m->render($templ, $ARR_notif_mail);


    // SEND MESSAGE TO EACH ADMINs
    $to = PUBLIC_NOTIFICATION_MAIL;
    $send_mail = mail::send_mail_by_PHPMailer( $to, $subject, $body );

    // RECORD MESSAGE IN DB
    message::record_message( $mail, $name,	$message,	$date_db );

    if( boolval($send_mail) == true ){

        // SUCCESS
        $tab = array('success' => tr::$TR['success_send_message'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;

    }
    else{

        // ERROR
        $tab = array('error' => tr::$TR['error_retry'] );
        echo json_encode($tab, JSON_FORCE_OBJECT);
        exit;
    }


	}
  /**
   * END mail::send_mail_contact();
   */



  /**
   * mail::get_all_admins_mail();
   *
   * @return {array}  return array of administrators emails
   */
  public static function get_all_admins_mail(){


      // GET ALL ADMINS MAILS
      $ARR_pdo = false;
      $sql = 'SELECT mail, name FROM admins';
      $response = 'all'; // multiples rows
      $last_id = false;

      $GET_ADMINS_MAIL = db::server($ARR_pdo, $sql, $response, $last_id);

      // prepa. admins mails array
      $ADMINS_mails = array();

      // loop
      foreach( $GET_ADMINS_MAIL as $v ){

          // fill $ADMINS_mails
          $ADMINS_mails[] = array(
            'mail' => $v['mail'],
            'name' => $v['name']
          );
      }
      // end loop

      // return admins mails array
      return $ADMINS_mails;

  }
  /**
   * mail::get_all_admins_mail();
   */



}
// end class mail::
?>
