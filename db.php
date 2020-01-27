<?php
/**
 * db - обертка над PDO
 * PHP Version 7.1
 * 
 * @author Buturlin Vitaliy (Byurrer), email: byurrer@mail.ru
 * @copyright 2019 Buturlin Vitaliy
 * @license MIT https://opensource.org/licenses/mit-license.php
 */

//##########################################################################

//! класс-обертка над PDO для работы с базой данных
class CDB
{
	//! объект PDO
	protected $m_DB = null;
	
	//! массив запросов
	protected $m_aStackSqlQuery = [];

	//! код ошибки (последней)
	protected $m_iErrorCode = 0;

	//! текст ошибки (последней)
	protected $m_sErrorText = "";

	//! строкас трассировкой стека, после ошибки (последней)
	protected $m_sTraceStack = "";

	//***************************************
	
	public function __construct($sHost, $sDBname, $sCharset, $sUser, $sPassword) 
	{
		$this->m_DB = null;
		$this->m_aStackSqlQuery = [];
		$this->m_iErrorCode = 0;
		$this->m_sErrorText = "";
		$this->m_sTraceStack = "";
		
		$aOpt  = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => TRUE,
		];

		$sDsn = 'mysql:host='.$sHost.';dbname='.$sDBname.';charset='.$sCharset;
		
		try
		{
			$this->m_DB = new PDO($sDsn, $sUser, $sPassword, $aOpt);
			$this->m_DB->exec("set names utf8");
		}
		catch(PDOException $oException) 
		{
			self::report($oException);
		}
	}
	
	//! возвращает массив осуществленных запросов (за всю жизнь экземпляра класса)
	public function getQueries()
	{
		return $this->m_aStackSqlQuery;
	}
	
	//! возвращает количество запросов (за всю жизнь экземпляра класса)
	public function getCountQueries()
	{
		return count($this->m_aStackSqlQuery);
	}
	
	//! возвращает строку последнего запроса
	public function getStrLastQuery()
	{
		if(count($this->m_aStackSqlQuery) > 0)
			return $this->m_aStackSqlQuery[count($this->m_aStackSqlQuery) - 1];
		return "not found queries ...";
	}
	
	//! установлено ли соединение с бд?
	public function isConnect()
	{
		return boolval($this->m_DB);
	}

	//! возвращает код ошибки (последней)
	public function getErrorCode()
	{
		return $this->m_iErrorCode;
	}

	//! возвращает текст ошибки (последней)
	public function getErrorText()
	{
		return $this->m_sErrorText;
	}

	//! возвращает строку трассировки стека после ошибки (последней)
	public function getStackTraceString()
	{
		return $this->m_sTraceStack;
	}

	//! возвращает последний вставленный id базы данных
	public function getLastInsertId()
	{
		return $this->m_DB->lastInsertId();
	}

	//**********************************************************************

	//! очистка дпнных для определения ошибки
	protected function clearErrorData()
	{
		$this->m_iErrorCode = 0;
		$this->m_sErrorText = "";
		$this->m_sTraceStack = "";
	}
	
	//! генерация сообщения об ошибке
	protected function report(PDOException $oException)
	{
		$this->m_iErrorCode = $oException->getCode();
		$this->m_sErrorText = $oException->getMessage();
		$this->m_sTraceStack = $oException->getTraceAsString();

		if(defined("__DEBUG") && __DEBUG)
		{
			echo $oException->getMessage() . "<br/>";
			if(count($this->m_aStackSqlQuery) > 0)
				echo "<pre>" . print_r($this->m_aStackSqlQuery, true) . "</pre>";
			echo "Stack trace: <br/>";
			$aError = explode("#", $oException->getTraceAsString());
			for($i=1, $il = count($aError); $i<$il; ++$i)
				echo " - " . $aError[$i] . "<br/>";
		}
	}

	//************************************************************************
	
	/*! запрос с получением массива
	*/
	public function query($sQuery, $aValues=null, $canRet=true) 
	{
		self::clearErrorData();

		try
		{
			$sSqlQuery = $sQuery;
			$this->m_aStackSqlQuery[] = $sSqlQuery;
			$this->m_DB->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);
			$oStmt = $this->m_DB->prepare($sSqlQuery);
			
			if($aValues)
			{
				foreach($aValues as $key => $value)
					$oStmt->bindValue($key, $value);
			}
			
			if($oStmt->execute() && $canRet)
				return $oStmt->fetchAll();
			else
				return null;
		} 
		catch(PDOException $oException) 
		{
			self::report($oException);
		}
	}
};

