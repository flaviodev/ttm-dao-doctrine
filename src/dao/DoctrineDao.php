<?php
namespace ttm_dao_doctrine\dao;

use Doctrine\ORM\EntityManager;
use ttm\model\ObjectBO;
use ttm\dao\Dao;
use Doctrine\ORM\Configuration;
use Doctrine\Common\Cache\ArrayCache;


class DoctrineDao implements Dao{
	private $entityManager;
	
	public function __construct(array $options) {
		$this->entityManager= $this->getEntityManager($options);
	}
	
	public function find($entityName, $key):ObjectBO {
		$em = $this->getEntityManager();
		return $em->find($entityName, $key);
	}
	
	public function findAll($entityName):array {
		$em = $this->getEntityManager();
		return $em->getRepository($entityName)->findAll();
	}
	
	public function create(ObjectBO $entity):ObjectBO {
		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush($entity);
		
		return $entity;
	}
	
	public function update(ObjectBO $entity) {
		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush($entity);
	}
	
	public function remove(ObjectBO $entity) {
		$em = $this->getEntityManager();
		$em->remove($entity);
		$em->flush($entity);
	}
		
	private function getEntityManager(array $options=null):EntityManager {
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
				
			$this->entityManager = EntityManager::create($dataConnection, $config);
		}
		
		
		return $this->entityManager;
	}
}