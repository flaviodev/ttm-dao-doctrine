<?php
namespace ttm_dao_doctrine\dao;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use ttm\model\ObjectBO;
use ttm\dao\Dao;

class DoctrineDao implements Dao{
	private $entityManager;
	
	public function __construct(array $config) {
		$this->entityManager= $this->getEntityManager($config);
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
		
	private function getEntityManager(array $config=null):EntityManager {
		if(is_null($this->entityManager) && !is_null($config)) {
			$doctrineConfig = Setup::createAnnotationMetadataConfiguration(array($config['entitiesPath']), $config['isDevMode']);
			$this->entityManager = EntityManager::create($config,$doctrineConfig);
		}
		
		return $this->entityManager;
	}
}