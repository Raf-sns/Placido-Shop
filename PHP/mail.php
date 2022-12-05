<?php
/**
 * PlACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello  2019-2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	mail.php
 *
 * CLASS mail::
 *
 * mail::send_mail_by_PHPMailer( $to, $subject, $message );
 * mail::send_order_to_customer( $SALE );
 * mail::send_notif_new_sale( $SALE );
 * mail::send_mail_contact();
 *
 */

class mail extends config {


  /**
   * mail::send_mail_by_PHPMailer( $to, $subject, $message );
   * send a mail by PHPMailer method
   * @Param $to -> mail to send
   * @Param $subject -> suject of mail
   * @Param $message -> html content with datas
   *  ? need -> classes/Exception.php - classes/PHPMailer.php - classes/SMTP.php
   *
   * @param  {str} $to        mail to send mail
   * @param  {str} $subject   subject of e-mail
   * @param  {str} $message   mail content in (x)html
   * @return {void}           send mail with PHPMailer library
   */
  public static function send_mail_by_PHPMailer( $to, $subject, $message ){

        // debug - un-comment this
        // $mail->SMTPDebug = 2;
        // error_reporting(E_STRICT | E_ALL);

				// include PHPMailer
        include_once ROOT.'/PHP/LIBS/PHPMailer/PHPMailer.php';
        include_once ROOT.'/PHP/LIBS/PHPMailer/SMTP.php';

        // SEND MAIL by PPHPMailer
        $mail = new PHPMailer();
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

        // Mail from -> public communication e-mail + domain name
        $mail->setFrom( PUBLIC_NOTIFICATION_MAIL, HOST );
        // Mail to send
        $mail->clearAddresses();
        $mail->addAddress($to);
        // Reply address
        $mail->addReplyTo(PUBLIC_NOTIFICATION_MAIL);
				// Mail in HTML or not
        $mail->isHTML(true);
				// Subject
        $mail->Subject = $subject;
				// Message
        $mail->Body = $message;
				// Message in text
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
				// More :
				// $mail->addCC('cc@example.com');
				// $mail->addBCC('bcc@example.com');
				// Add multiples attachments
				// $mail->addAttachment('/var/tmp/file.tar.gz');
				// $mail->addAttachment('/tmp/image.jpg', 'new.jpg');

        // SEND
        if( !$mail->send() ){

           // render error
           echo 'Mailer Error: '.$mail->ErrorInfo;
           return;
        }

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


    // DATES i.-> can remove delivery date &/ payment limit date -> just comment
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

    // // use .html instead of .mustache for default template extension
    $options =  array('extension' => '.html');
    // // template loader
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
   * @return {void}   send notification mail new sale to the seller
   */
  public static function send_notif_new_sale( $SALE ){


      // send message new sale to vendor at the public notifications mail
      $to = PUBLIC_NOTIFICATION_MAIL;

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


    // start session for retrieve ARRay of datas for AJAX object
    session_start([
        'name' => 'PLACIDO-SHOP',
        'use_strict_mode' => true,
        'cookie_samesite' => 'Strict',
        'cookie_lifetime' => 1200, // 20min.
        'gc_maxlifetime' => 1200,
        'cookie_secure' => true,
        'cookie_httponly' => true
    ]);

    // put a session to not repost multiple
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
    $name = trim(htmlspecialchars($_POST['name']));
    $mail = trim(htmlspecialchars($_POST['mail']));
    $mail = filter_var($mail, FILTER_SANITIZE_EMAIL);
    $message = trim(htmlspecialchars($_POST['message']));

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

        // ERROR
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


}
// END CLASS mail::


?>
