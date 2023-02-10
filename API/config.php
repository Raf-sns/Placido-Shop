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
	protected const DB_HOST = "localhost";
	protected const DB_NAME = "tpvb2265_Placido-DEV";
	protected const DB_USER = "tpvb2265_Uzz3Rrr-H4Lo-Koi-K4LMOS-Ax0loTL";
	protected const DB_PASSWORD = "MeccA-Niks-F0r_P3Ac3";

	// public CONSTANTS MAILBOX NOTIFICATIONS - this is (const) !!!
	protected const MAILBOX_HOST = "sns.pm";
	protected const MAILBOX_ACCOUNT = "contact@sns.pm";
	protected const MAILBOX_PASSW = "klopùùù";
	protected const MAILBOX_PORT = "465";

	// protected CRYPTO KEYS - called by class api
	// ! DO NOT MODIFY THESE KEYS IN PRODUCTION !
	protected const SEC_API_KEY = "ojiosdjfsdbtfgyitfdoepazmqsfhisghfigiergleifmrghierglzbeuvuyuluybwuybvlwjqophauhzoihbsifbquizbfqbfubgrbjsebgjebgebrgul";
	protected const SEC_API_IV = "absdfnbhsbdouyoogiebqrbgliqbgliebrglierbglbfvbdjfbvjdbfjshdblfvjbsdlvblb";

}
// end class config

?>
