<?php

require_once(__DIR__."/../model/Stock.php");
require_once(__DIR__."/../database/StockDAO.php");
require_once(__DIR__."/../rest/BaseRest.php");
require_once(__DIR__."/../components/ServerWrapper.php");

/**
 * Clase que recibe las peticiones relacionadas con la gestión de saldos. Se
 * comunica con otros componentes del servidor para realizar las acciones
 * solicitadas por el cliente y le envía una respuesta acorde al resultado
 * obtenido de la realización de las acciones solicitadas.
 *
 * @author acfernandez4 <acfernandez4@esei.uvigo.es>
 */ 

class StockRest extends BaseRest
{
	private $stockDAO;

	public function __construct()
	{
        parent::__construct();
        $this->stockDAO = new StockDAO();
	}

	public function create($data)
    {
    	$currentUser = parent::authenticateUser();
    	$stock = new Stock();

    	if (isset($data->total) && isset($data->date)) {
        	$stock->setDate($data->date);
    		$stock->setTotal($data->total);
    		$stock->setOwner($currentUser->getLogin());
    	
    	
	    	try {
	    		$idStock = $this->stockDAO->save($stock);
	    		header($this->server->getServerProtocol() .' 201 Created');
	      		header('Location: '. $this->server->getRequestUri() ."/".$idStock);
	      		header('Content-Type: application/json');

	    	} catch (ValidationException $e) {
	    		header($this->server->getServerProtocol() .' 400 Bad request');
	      		echo(json_encode($e->getErrors()));
	    	}
	    }
	}

	public function update($idStock, $data)
	{
		$currentUser = parent::authenticateUser();

		$stock = $this->stockDAO->findById($idStock);
		if ($stock == NULL) {
      		header($this->server->getServerProtocol().' 400 Bad request');
      		echo("Stock with id ".$idStock." not found");
      		return;
    	}


    	if($stock->getOwner()->getLogin() != $currentUser->getLogin()) {
    		header($this->server->getServerProtocol().' 403 Forbidden');
      		echo("you are not the owner of this stock");
      		return;
    	}

    	if (isset($data->total) && isset($data->date)) {
        	$stock->setDate($data->date);
    		$stock->setTotal($data->total);

    		try {
      			$this->stockDAO->update($stock);
      			header($this->server->getServerProtocol() .' 200 Ok');
    		}catch (ValidationException $e) {
      			header($this->server->getServerProtocol().' 400 Bad request');
      			echo(json_encode($e->getErrors()));
    		}
    	}
	}


	public function delete($idStock)
	{
		$currentUser = parent::authenticateUser();
		
		$stock = $this->stockDAO->findById($idStock);
		if ($stock == NULL) {
      		header($this->server->getServerProtocol().' 400 Bad request');
      		echo("Stock with id ".$idStock." not found");
      		return;
    	}

    	if($stock->getOwner()->getLogin() != $currentUser->getLogin()) {
    		header($this->server->getServerProtocol().' 403 Forbidden');
      		echo("you are not the owner of this stock");
      		return;
    	}

    	try {
      		$this->stockDAO->delete($idStock);
      		header($this->server->getServerProtocol() .' 200 Ok');
    	}catch (ValidationException $e) {
      		header($this->server->getServerProtocol() .' 400 Bad request');
      		echo(json_encode($e->getErrors()));
    	}
	}


	public function getByOwner($owner)
	{

		$currentUser = parent::authenticateUser();

        $startDate = $this->request->getStartDate();
        $endDate = $this->request->getEndDate();
   
        $stocks = $this->stockDAO->findByOwnerAndFilter($owner, $startDate, $endDate);
        if ($stocks == NULL) {
            header($this->server->getServerProtocol() . ' 400 Bad request');
            echo("The defined interval time not contains stocks");
        	return;
        }

        foreach ($stocks as $stock) {
            if ($stock->getOwner()->getLogin() != $currentUser->getLogin()) {
                header($this->server->getServerProtocol() . ' 403 Forbidden');
                echo("you are not the owner of this stock");
                return;
            }
        }

        $stock_array = [];
        foreach ($stocks as $stock) {
            array_push($stock_array, [
           	    "idStock" => $stock->getIdStock(),
                "date" => $stock->getDate(),
                "total" => $stock->getTotal(),
                "owner" => $stock->getOwner()->getLogin()
            ]);
        }
		header($this->server->getServerProtocol() .' 200 Ok');
    	header('Content-Type: application/json');
    	echo(json_encode($stock_array));
	}

}

$stockRest = new StockRest();
URIDispatcher::getInstance()
    ->map("GET", "/stocks/$1", array($stockRest, "getByOwner"))	
    ->map("POST", "/stocks", array($stockRest,"create"))
	->map("PUT", "/stocks/$1", array($stockRest, "update"))
	->map("DELETE", "/stocks/$1", array($stockRest, "delete"));
?>
