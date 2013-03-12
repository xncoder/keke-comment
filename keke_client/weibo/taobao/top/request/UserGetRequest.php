<?php
/**
 * TOP API: taobao.user.get request
 * 
 * @author auto create
 * @since 1.0, 2011-06-14 14:08:23.0
 */
class UserGetRequest
{
	 
	private $fields;
	
	 
	private $nick;
	
	private $apiParas = array();
	
	public function setFields($fields)
	{
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function setNick($nick)
	{
		$this->nick = $nick;
		$this->apiParas["nick"] = $nick;
	}

	public function getNick()
	{
		return $this->nick;
	}

	public function getApiMethodName()
	{
		return "taobao.user.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
}
