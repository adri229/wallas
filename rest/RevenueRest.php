<?php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../database/UserDAO.php");

require_once(__DIR__."/../model/Revenue.php");
require_once(__DIR__."/../database/RevenueDAO.php");


require_once(__DIR__."/../rest/BaseRest.php");


class RevenueRest extends BaseRest
{
	private $revenueDAO;
	
	function __construct()
	{
		parent::__construct();
		$this->revenueDAO = new RevenueDAO();
	}

	public function create($data)
    {
    	$currentUser = parent::authenticateUser();
    	$revenue = new Revenue();

    	if (isset($data->quantity) && isset($data->name)) {
    		$revenue->setQuantity($data->quantity);
        $revenue->setName($data->name);
    		$revenue->setOwner($currentUser->getLogin());
    	
    	
	    	try {
	    		//$revenue->validate();	
	    		  $idRevenue = $this->revenueDAO->save($revenue);
	    		  header($_SERVER['SERVER_PROTOCOL'].' 201 Created');
	      		header('Location: '.$_SERVER['REQUEST_URI']."/".$idRevenue);
	      		
	    	} catch (ValidationException $e) {
	    		header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
	      		echo(json_encode($e->getErrors()));
	    	}
	    }
	}

	public function update($idRevenue, $attribute, $data)
	{
		$currentUser = parent::authenticateUser();

		$revenue = $this->revenueDAO->findById($idRevenue);
		if ($revenue == NULL) {
      		header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
      		echo("Revenue with id ".$idRevenue." not found");
      		return;
    	}


    	if($revenue->getOwner()->getLogin() != $currentUser->getLogin()) {
    		header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
      		echo("you are not the owner of this revenue");
      		return;
    	}


      switch ($attribute) {
        case 'quantity':
          $revenue->setQuantity($data->quantity);
          break;
        case 'name':
          $revenue->setName($data->name);
          break;
        default:
          break;
      }

    	try {
            // validate Post object
            //$revenue->validate(); // if it fails, ValidationException
            $this->revenueDAO->update($revenue);
            header($_SERVER['SERVER_PROTOCOL'].' 200 Ok');
        }catch (ValidationException $e) {
            header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
            echo(json_encode($e->getErrors()));
        }
	}

	public function delete($idRevenue)
	{
		$currentUser = parent::authenticateUser();
		
		$revenue = $this->revenueDAO->findById($idRevenue);
		if ($revenue == NULL) {
      		header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
      		echo("Revenue with id ".$idRevenue." not found");
      		return;
    	}


    	if($revenue->getOwner()->getLogin() != $currentUser->getLogin()) {
    		header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
      		echo("you are not the owner of this revenue");
      		return;
    	}

    	try {
      		$this->revenueDAO->delete($idRevenue);
      		header($_SERVER['SERVER_PROTOCOL'].' 200 Ok');
    	}catch (ValidationException $e) {
      		header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
      		echo(json_encode($e->getErrors()));
    	}	
	}

	public function getByOwner($owner)
	{
		$currentUser = parent::authenticateUser();

		$startDate = $_GET["startDate"];
		$endDate = $_GET["endDate"];

		$revenues = $this->revenueDAO->findByOwnerAndFilter($owner, $startDate, $endDate);

		if ($revenues == NULL) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad request');
            echo("The defined interval time not contains revenues");
            return;
        }

        foreach ($revenues as $revenue) {
            if ($revenue->getOwner()->getLogin() != $currentUser->getLogin()) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
                echo("you are not the owner of this revenue");
                return;
            }
        }
		$revenue_array = [];
		foreach ($revenues as $revenue) {
			array_push($revenue_array, [
				"idRevenue" => $revenue->getIdRevenue(),
				"dateRevenue" => $revenue->getDateRevenue(),
				"quantity" => $revenue->getQuantity(),
        "name" => $revenue->getName(),
				"owner" => $revenue->getOwner()->getLogin()
			]);
		}

		header($_SERVER['SERVER_PROTOCOL'].' 200 Ok');
    	header('Content-Type: application/json');
    	echo(json_encode($revenue_array));
	}


}

$revenueRest = new RevenueRest();
URIDispatcher::getInstance()
	->map("GET", "/revenues/$1", [$revenueRest, "getByOwner"])	
	->map("POST", "/revenues", [$revenueRest,"create"])
	->map("PUT", "/revenues/$1/$2", [$revenueRest, "update"])
	->map("DELETE", "/revenues/$1", [$revenueRest, "delete"]);

?>
