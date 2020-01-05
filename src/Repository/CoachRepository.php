<?php

namespace App\Repository;

use App\Entity\Coach;
use App\Entity\CoachSearch;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Coach|null find($id, $lockMode = null, $lockVersion = null)
 * @method Coach|null findOneBy(array $criteria, array $orderBy = null)
 * @method Coach[]    findAll()
 * @method Coach[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoachRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coach::class);
    }

    public function findGoodCoach(CoachSearch $search){

        $query = $this->createQueryBuilder('c');
        
        if($search->getVille()){
            $query = $query->andWhere('c.ville = :ville');
            $query ->setParameter('ville',$search->getVille());
        }

        if ($search->getSport()){
            $query = $query->andWhere('c.domaine = :sport');
            $query ->setParameter('sport',$search->getSport()); 
        }

        return $query->getQuery();
    }



    // /**
    //  * @return Coach[] Returns an array of Coach objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Coach
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
