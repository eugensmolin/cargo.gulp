<?php

require_once "./service/config.php";
require_once "./service/service.php";

class Payment
{
    public $iOrderID	= 0;
    public $sBankID		= '';
    public $sFormUrl	= '';
    public $isPayed	    = '';
    public $isStatusOK	= false;
    public $isExist     = false;
    public $isError     = false;
    public $sStatus     = '';
    public $dExpirationDate = 0;

    public function Create($oDBHandler,
                           $iOrderId,
                           $sBankOrderId,
                           $sFormUrl,
                           $expirationDate)
    {

        if(!isset($iOrderId) || $iOrderId==0)
        {
            $this->isStatusOK  = false;
            $this->isError = true;
            return 'Order not set';
        }

       /* if(!isset($sBankOrderId) || $sBankOrderId=='')
        {
            $this->isStatusOK  = false;
            $this->isError = true;
            return 'BankOrderId not set';
        }*/

        if(!isset($sFormUrl) || $sFormUrl=='')
        {
            $this->isStatusOK  = false;
            return 'FormUrl not set';
        }

        $sQuery = "INSERT INTO `" . DB_ORDER_PAYMENT . "`
				(orderId, alfaOrderId, status,isPayed, formUrl,expirationDate)
				VALUES ($iOrderId, '".$sBankOrderId."', 'new',0, '".$sFormUrl."','".$expirationDate."')";

        $oResult = $oDBHandler->query($sQuery);

        if(!IS_PRODUCTION)
        {
            echo $sQuery,'<br>';
        }

        $this->sFormUrl = $sFormUrl;
        $this->iOrderID = $iOrderId;
        $this->dExpirationDate = $expirationDate;
        $this->sBankID = $sBankOrderId;

        return '';
    }

    public function Reject($oDBHandler, $iOrderId)
    {
        if(!isset($iOrderId) || $iOrderId==0)
        {
            $this->isStatusOK  = false;
            return 'Order not set';
        }

        $sQuery = "UPDATE order_payment
								SET isPayed=0,
								status = 'reject'
								WHERE orderId=".$iOrderId;

        $oResult = $oDBHandler->query($sQuery);

        return '';
    }

    public function Info($oDBHandler, $iOrderId)
    {

        if(!isset($iOrderId) || $iOrderId==0)
        {
            $this->isStatusOK = false;
            $this->isError = true;
            return 'Order not set';
        }

        $sSearchQuery = "SELECT orderId, alfaOrderId, isPayed, formUrl,status,UNIX_TIMESTAMP(expirationDate) expirationDate
							 FROM `" . DB_ORDER_PAYMENT . "`
							 WHERE orderId=".$iOrderId;

        $oSearchResult = $oDBHandler->query($sSearchQuery);

        if ($oDBHandler->affected_rows > 0)
        {
            $oRow = $oSearchResult->fetch_assoc();

            $this->isStatusOK = true;
            $this->isExist = true;
            $this->iOrderID = (int)$oRow["orderId"];
            $this->sBankID = $oRow["alfaOrderId"];
            $this->isPayed = (int)$oRow["isPayed"];
            $this->sFormUrl = $oRow["formUrl"];
            $this->sStatus = $oRow["status"];
            $this->dExpirationDate=$oRow["expirationDate"];
            return '';
        }
        else
        {
            $this->isStatusOK = false;
            $this->isExist = false;
            return 'Data not exist';
        }
    }

    public function Refund($oDBHandler, $iOrderId)
    {

        if(!isset($iOrderId) || $iOrderId==0)
        {
            $this->isStatusOK  = false;
            return 'Order not set';
        }

        $sQuery = "UPDATE order_payment
								SET isPayed = 0,
								status ='refound'
								WHERE orderId=".$iOrderId;

        $oResult = $oDBHandler->query($sQuery);

        return '';
    }

    public function Finish($oDBHandler, $iOrderId, $transactionNumber="")
    {

        if(!isset($iOrderId) || $iOrderId==0)
        {
            $this->isStatusOK  = false;
            return 'Order not set';
        }

        $sQuery = "";

        if($transactionNumber=="")
        {
            $sQuery = "UPDATE order_payment
								SET isPayed = 1,
								status ='payed'
								WHERE orderId=".$iOrderId;
        }
        else
        {
            $sQuery = "UPDATE order_payment
								SET isPayed = 1,
								status ='payed',
								alfaOrderId = $transactionNumber
								WHERE orderId=".$iOrderId;
        }



        $oSearchResult = $oDBHandler->query($sQuery);

        return '';
    }

 }

?>
