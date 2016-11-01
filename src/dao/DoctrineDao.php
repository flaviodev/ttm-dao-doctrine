<?php
namespace ttm_dao_doctrine\dao;

use Doctrine\ORM\EntityManager;
use ttm\model\Model;
use ttm\dao\Dao;
use Doctrine\ORM\Configuration;
use Doctrine\Common\Cache\ArrayCache;

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
	public function find($entity, $id):Model {
		$em = $this->getEntityManager();
		return $em->find($entity, $id);
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
	public function findAll($entity):array {
		$em = $this->getEntityManager();
		return $em->getRepository($entity)->findAll();
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
	public function create(Model $entity):Model {
		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush($entity);
		
		return $entity;
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
	public function getEntityManager(array $options=null) {
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
				
			$this->entityManager = EntityManager::create($dataConnection, $config);
		}
		
		return $this->entityManager;
	}
}