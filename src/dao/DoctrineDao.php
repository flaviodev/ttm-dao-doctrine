<?php
namespace ttm_dao_doctrine\dao;

use Doctrine\ORM\EntityManager;
use ttm\model\ObjectBO;
use ttm\dao\Dao;
use Doctrine\ORM\Configuration;
use Doctrine\Common\Cache\ArrayCache;

/**
 * DoctrineDao - Implementation of the ttm\dao\Dao using doctrine/orm
 *
 * @author flaviodev - Flávio de Souza - fdsdev@gmail.com
 * @version 1.0
 * @since 1.0
 * @package ttm-dao-docrine
 * @namespace ttm_dao_doctrine\dao
 */
class DoctrineDao implements Dao{
	/**
	 * @property has the Doctrine's entity manager (Doctrine\ORM\EntityManager)
	 *
	 * @since 1.0
	 * @access private
	 */
	private $entityManager;
	
	/**
	 * @method constructor of class
	 *
	 * @since 1.0
	 * @magic
	 * @access public
	 * @param array $options - has the options and configurations for 
	 * create the Doctrine's entity manager (Doctrine\ORM\EntityManager)
	 * required sets of array: driver, host, dbname, user, password, charset,modelDir,proxyDir,proxyNamespace,autoGenerateProxyClasses
	 */
	public function __construct(array $options) {
		$this->entityManager= $this->getEntityManager($options);
	}
	
	/**
	 * @method Return a mapped object on data base (orm) corresponding to a type of
	 * class (entity) and a id informed. Uses find method of EntityManager.
	 *
	 * @since 1.0
	 * @access public
	 * @param $entity - class of object (entity) mapped on data base
	 * @param $id - primary key for find register on data base
	 * @return ObjectBO - mapped object fill with data
	 */
	public function find($entity, $id):ObjectBO {
		$em = $this->getEntityManager();
		return $em->find($entity, $id);
	}
	
	/**
	 * @method Return all mapped objects on data base (orm) corresponding to a 
	 * type of class (entity). Uses methods getRepository and findAll of EntityManager.
	 *
	 * @since 1.0
	 * @access public
	 * @param $entity - class of object (entity) mapped on data base
	 * @return array - mapped objects fill with data
	 */
	public function findAll($entity):array {
		$em = $this->getEntityManager();
		return $em->getRepository($entity)->findAll();
	}

	/**
	 * @method Update data base register associated to mapped entity. Uses methods  
	 * persist and flush of EntityManager.
	 *
	 * @since 1.0
	 * @access public
	 * @param ObjectBO $entity - Object (entity) mapped on data base
	 */
	public function update(ObjectBO $entity) {
		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush($entity);
	}

	/**
	 * @method Remove (delete) data base register associated to mapped entity. Uses methods  
	 * remove and flush of EntityManager.
	 *
	 * @since 1.0
	 * @access public
	 * @param ObjectBO $entity - Object (entity) mapped on data base
	 */
	public function remove(ObjectBO $entity) {
		$em = $this->getEntityManager();
		$em->remove($entity);
		$em->flush($entity);
	}
	
	/**
	 * @method Create (insert) data base register associated to mapped entity. Uses methods  
	 * persist and flush of EntityManager.
	 *
	 * @since 1.0
	 * @access public
	 * @param ObjectBO $entity - Object (entity) mapped on data base
	 * @return ObjectBO - Object (entity) mapped on data base after register on
	 * data base, that have all data on data base (example: auto-generated id)
	 */
	public function create(ObjectBO $entity):ObjectBO {
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
	 * @since 1.0
	 * @access public
	 * @param array $options - has the options and configurations for 
	 * create the Doctrine's entity manager (Doctrine\ORM\EntityManager)
	 * required sets of array: driver, host, dbname, user, password, charset,modelDir,proxyDir,
	 * proxyNamespace,autoGenerateProxyClasses
	 */
	public function getEntityManager(array $options=null) {
		if(is_null($this->entityManager) && !is_null($options)) {
						 
			$config = new Configuration();
			$cache = new ArrayCache();
			$driverImpl = $config->newDefaultAnnotationDriver($options['modelDir']);
			
			$config->setMetadataCacheImpl($cache);
			$config->setQueryCacheImpl($cache);
			$config->setProxyDir($options['proxyDir']);
			$config->setProxyNamespace($options['proxyNamespace']);
			$config->setAutoGenerateProxyClasses($options['autoGenerateProxyClasses']);
			$config->setMetadataDriverImpl($driverImpl);
			
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