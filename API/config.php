<?php
/**
 * PlACIDO-SHOP FRAMEWORK - FRONT
 * Copyright © Raphaël Castello, 2022
 * Organisation: SNS - Web et Informatique
 * Web site: https://sns.pm
 * @link: contact@sns.pm
 *
 * Script name:	 config.php
 *
 * Class config
 * ( extends Class API/api.php - API/db.php - PHP/mail.php )
 * Here set constants of your application
 *
 */

class config {


	// protected CONSTANTS DATA BASE - this is (const) !!!
	protected const DB_HOST = "";
	protected const DB_NAME = "";
	protected const DB_USER = "";
	protected const DB_PASSWORD = "";

	// public CONSTANTS MAILBOX NOTIFICATIONS - this is (const) !!!
	protected const MAILBOX_HOST = "";
	protected const MAILBOX_ACCOUNT = "";
	protected const MAILBOX_PASSW = "";
	protected const MAILBOX_PORT = "";

	// protected CRYPTO KEYS - called by class api
	// ! DO NOT MODIFY THESE KEYS IN PRODUCTION !
	protected const SEC_API_KEY = "";
	protected const SEC_API_IV = "";
	
}
// end class config

?>
