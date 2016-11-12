<?php
namespace ttm_dao_doctrine\dao;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use ttm\dao\Dao;
use ttm\model\Model;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * Class DoctrineDao - Implementation of the ttm\dao\Dao using doctrine/orm
 *
 * @see ttm\dao\Dao
 *
 * @package ttm-dao-docrine
 * @namespace ttm_dao_doctrine\dao
 * @version 1.0
 */
class DoctrineDao implements Dao{
	/**
	 * @property has the Doctrine's entity manager (Doctrine\ORM\EntityManager)
	 *
	 * @access private
	 * @since 1.0
	 */
	private $entityManager;

	/**
	 * @property has the parameters for connecting with the database
	 *
	 * @access private
	 * @since 1.0
	 */
	private $connectionParameters;

	/**
	 * @property has the configurations for connecting with the database
	 *
	 * @access private
	 * @since 1.0
	 */
	private $connectionConfig;


	private $searchesWithLocaleOrder;

	/**
	 * @method constructor of class
	 *
	 * @param array $options - has the options and configurations for
	 * create the Doctrine's entity manager (Doctrine\ORM\EntityManager)
	 * required sets of array: driver, host, dbname, user, password, charset,
	 * modelDir,proxyDir,proxyNamespace,autoGenerateProxyClasses
	 *
	 * @magic
	 * @access public
	 * @since 1.0
	 */
	public function __construct(array $options) {
		$this->entityManager= $this->getEntityManager($options);
	}

	/**
	 * @method Return a mapped object on data base (orm) corresponding to a type of
	 * class (entity) and a id informed. Uses find method of EntityManager.
	 *
	 * @param $entity - class of object (entity) mapped on data base
	 * @param $id - primary key for find register on data base
	 * @return ttm\model\Model - mapped object fill with data
	 *
	 * @access public
	 * @since 1.0
	 */
	public function find($entity, $id, $locale=null, $localeOnly=false) {
		$em = $this->getEntityManager();

		if(is_null($locale)) {
			return $em->find($entity, $id);
		}

		$entityQuery = "SELECT e, s FROM ".$entity." e JOIN e.localeStrings s WHERE e.id=".$id." AND s.locale='".$locale."'";

		$result = $this->getResult($entityQuery);

		if(sizeof($result)>0) {
			return $result[0];
		}

		if($localeOnly) {
			return null;
		}

		foreach ($this->searchesWithLocaleOrder as $searchLocale) {
			if($locale != $searchLocale) {
				$entityQuery = "SELECT e, s FROM ".$entity." e JOIN e.localeStrings s WHERE e.id=".$id." AND s.locale='".$searchLocale."'";

				$result = $this->getResult($entityQuery);

				if(sizeof($result)>0) {
					return $result[0];
				}
			}
		}

		return null;
	}


	/**
	 * @method Return all mapped objects on data base (orm) corresponding to a
	 * type of class (entity). Uses methods getRepository and findAll of EntityManager.
	 *
	 * @param $entity - class of object (entity) mapped on data base
	 * @return array - mapped objects fill with data
	 *
	 * @access public
	 * @since 1.0
	 */
	public function findAll($entity, $locale=null, $localeOnly=false){
		$em = $this->getEntityManager();

		if(is_null($locale)) {
			return $em->getRepository($entity)->findAll();
		}

		if($localeOnly) {
			$entityQuery = "SELECT e, s FROM ".$entity." e JOIN e.localeStrings s WHERE s.locale='".$locale."'";
				
			$result = $this->getResult($entityQuery);
				
				
			if(sizeof($result)==0) {
				return null;
			}
				
			return $result;
		}

		$ids = array();
		$entities = $em->getRepository($entity)->findAll();

		foreach ($entities as $item) {
			array_push($ids, $item->getId());
		}

		$data = array();

		$entityQuery = "SELECT e, s FROM ".$entity." e JOIN e.localeStrings s WHERE s.locale='".$locale."'";

		$result = $this->getResult($entityQuery);

		foreach ($result as $item) {
			array_push($data, $item);
			$index = array_search($item->getId(),$ids);
			unset($ids[$index]);
		}

		if(sizeOf($ids)==0) {
			return $data;
		}

		foreach ($this->searchesWithLocaleOrder as $searchLocale) {
			if($locale != $searchLocale) {
				$entityQuery = "SELECT e, s FROM ".$entity." e JOIN e.localeStrings s WHERE s.locale='".$searchLocale."'";

				$result = $this->getResult($entityQuery);

				foreach ($result as $item) {
					$index = array_search($item->getId(),$ids);

					if($index>-1){
						array_push($data, $item);
						unset($ids[$index]);
					}
				}

				if(sizeOf($ids)==0) {
					return $data;
				}
			}
		}

		return $data;
	}

	/**
	 * @method Update data base register associated to mapped entity. Uses methods
	 * persist and flush of EntityManager.
	 *
	 * @param ttm\model\Model $entity - Object (entity) mapped on data base
	 *
	 * @access public
	 * @since 1.0
	 */
	public function update(Model $entity) {
		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush($entity);

		return $entity;
	}

	/**
	 * @method Remove (delete) data base register associated to mapped entity. Uses methods
	 * remove and flush of EntityManager.
	 *
	 * @param ttm\model\Model $entity - Object (entity) mapped on data base
	 *
	 * @access public
	 * @since 1.0
	 */
	public function remove(Model $entity) {
		$em = $this->getEntityManager();
		$em->remove($entity);
		$em->flush($entity);
	}

	/**
	 * @method Create (insert) data base register associated to mapped entity. Uses methods
	 * persist and flush of EntityManager.
	 *
	 * @param ttm\model\Model $entity - Object (entity) mapped on data base
	 * @return ttm\model\Model - Object (entity) mapped on data base after register on
	 * data base, that have all data on data base (example: auto-generated id)
	 *
	 * @access public
	 * @since 1.0
	 */
	public function create(Model $entity) {
		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush($entity);

		return $entity;
	}

	/**
	 * @method getResult - returns registers associated to mapped entity based on a query
	 * on entity manager using DQL - doctrine query language
	 *
	 * @param string $entityQuery - a select using DQL
	 * @param array $parameters - array of parameter for query
	 * @return an array with the objects (entity) returned by query
	 *
	 * @example 'SELECT u FROM MyProject\Model\User u WHERE u.age > 20'
	 *
	 * @access public
	 * @since 1.0
	 **/
	public function getResult(string $entityQuery, array $parameters=null) {
		$em = $this->getEntityManager();
		$query = $em->createQuery($entityQuery);

		if(!is_null($parameters)) {
			$query->setParameters($parameters);
		}

		return  $query->getResult();
	}

	/**
	 * @method getResultSet - returns an array of the registers on database using a
	 * sql query
	 *
	 * @param string $sql - a select using sql
	 * @param array $parameters - array of parameter for query
	 * @return an array with the data returned by query
	 *
	 * @access public
	 * @since 1.0
	 **/
	public function getResultSet(string $sql, array $parameters=null){
		try {
			$connection = $this->getConnection();
			$connection->connect();
				
			$statement = $connection->prepare($sql);

			if(!is_null($parameters)) {
				$i=1;
				foreach ($parameters as $parameter) {
					$statement->bindValue($i++, $parameter);
				}
			}
				
			$statement->execute();
				
			return $statement->fetchAll();
		} finally {
			$connection->close();
		}
	}

	/**
	 * @method Create a instance of the Doctrine\ORM\EntityManager. Encapsulating the
	 * configuration of: Implementation onf metadata cache, informations for proxies
	 * generation(autoGenerate) and connection with the dbms (data base management system)
	 *
	 * @param array $options - has the options and configurations for
	 * create the Doctrine's entity manager (Doctrine\ORM\EntityManager)
	 * required sets of array: driver, host, dbname, user, password, charset,modelDir,proxyDir,
	 * proxyNamespace,autoGenerateProxyClasses
	 *
	 * @access public
	 * @since 1.0
	 */
	private function getEntityManager(array $options=null) {
		if(is_null($this->entityManager) && !is_null($options)) {
				
			// TODO must be implement validations of requiment sets and throws exception
				
			$config = new Configuration();
			$cache = new ArrayCache();
			// configuring the path of model classes
			$driverImpl = $config->newDefaultAnnotationDriver($options['modelDir']);
				
			// configuring cache
			$config->setMetadataCacheImpl($cache);
			$config->setQueryCacheImpl($cache);
				
			//configuring data proxies, por auto generation de proxies (lazy-load)
			$config->setProxyDir($options['proxyDir']);
			$config->setProxyNamespace($options['proxyNamespace']);
			$config->setAutoGenerateProxyClasses($options['autoGenerateProxyClasses']);
			$config->setMetadataDriverImpl($driverImpl);
			$this->connectionConfig = $config;

			$this->searchesWithLocaleOrder=$options['searchesWithLocaleOrder'];
				
			/**
			 * @var array $dataConnection
			 * creating other array for database configuration
			 * just for use a unique parameter on method (for dont have two arrays)
			 */
			$dataConnection = array();
			$dataConnection["driver"] = $options["driver"];
			$dataConnection["host"] = $options["host"];
			$dataConnection["dbname"] = $options["dbname"];
			$dataConnection["user"] = $options["user"];
			$dataConnection["password"] = $options["password"];
			$dataConnection["charset"] = $options["charset"];
				
			//keep parameters for connection
			$this->connectionParameters = $dataConnection;
				
			$this->entityManager = EntityManager::create($dataConnection, $config);
		}

		return $this->entityManager;
	}

	private function getConnection():Connection {
		return DriverManager::getConnection($this->connectionParameters, $this->connectionConfig);
	}

}