<?php

/****************************************************************************************
* LiveZilla image.php
* 
* Copyright 2014 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors. 

* 
***************************************************************************************/ 

define("IN_LIVEZILLA",true);

if(!defined("LIVEZILLA_PATH"))
	define("LIVEZILLA_PATH","./");

require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");

require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.protocol.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.external.inc.php");

@set_error_handler("handleError");
@error_reporting(E_ALL);

header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Keep-Alive: timeout=5, max=100");

Server::InitDataProvider();
if(!empty($_GET["value"]) && strlen($_GET["value"])==16)
{
	$ticket = VisitorChat::GetMatchingVoucher($_GET[GET_EXTERN_GROUP],$_GET["value"]);
	if(!empty($ticket) && !$ticket->CheckForVoid() && $ticket->Paid)
	{
		$ticket->UpdateVoucherChatTime(0,empty($ticket->FirstUsed));
		$sessions = ($ticket->ChatSessionsMax < 0) ? 0 : $ticket->ChatSessionsMax;
		
		if($result = DBManager::Execute(true,"SELECT `exit` FROM `".DB_PREFIX.DATABASE_VISITOR_CHATS."` WHERE `chat_ticket_id`='".DBManager::RealEscape($ticket->Id)."' AND `exit`=0 LIMIT 1;"))
			if($row = DBManager::FetchArray($result))
				exit("lz_validate_com_chat_input_result(false,true,1,'',0,0,0,false,false,false);");
		exit("lz_validate_com_chat_input_result(true,false,1,'".$ticket->Id."',".$ticket->ChatTime.",".$ticket->ChatTimeMax.",".$ticket->ChatSessions.",".$ticket->ChatSessionsMax.",".$ticket->VoucherAutoExpire.",".To::BoolString($ticket->VoucherAutoExpire < time()).");");
	}
	else if(!empty($ticket))
		exit("lz_validate_com_chat_input_result(false,false,1,'',0,0,0,false,false,false);");
}
exit("lz_validate_com_chat_input_result(false,false,0,'',0,0,0,false,false,false);");
Server::UnloadDataProvider();
?>