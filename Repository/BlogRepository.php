<?php

namespace Plugin\SimpleBlog42\Repository;

use Eccube\Repository\AbstractRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry as RegistryInterface;
use Plugin\SimpleBlog42\Entity\Blog;

/**
 * BlogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BlogRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Blog::class);
    }

    /**
     * 新着情報を登録します.
     *
     * @param $Blog
     */
    public function save($Blog)
    {
        $em = $this->getEntityManager();
        $em->persist($Blog);
        $em->flush();
    }

    /**
     * 新着情報を削除します.
     *
     * @param News $Blog
     *
     * @throws ForeignKeyConstraintViolationException 外部キー制約違反の場合
     * @throws DriverException SQLiteの場合, 外部キー制約違反が発生すると, DriverExceptionをthrowします.
     */
    public function delete($Blog)
    {
        $em = $this->getEntityManager();
        $em->remove($Blog);
        $em->flush();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderAll()
    {
        $qb = $this->createQueryBuilder('n');
        $qb->orderBy('n.publish_date', 'DESC')
            ->addOrderBy('n.id', 'DESC');

        return $qb;
    }

    /**
     * @return News[]|ArrayCollection
     */
    public function getList()
    {
        // second level cacheを効かせるためfindByで取得
        $Results = $this->findBy(['visible' => true], ['publish_date' => 'DESC', 'id' => 'DESC']);

        // 公開日時前のNewsをフィルター
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->lte('publish_date', new \DateTime()));

        $News = new ArrayCollection($Results);

        return $News->matching($criteria);
    }


    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderPublished()
    {
        $qb = $this->createQueryBuilder('n');
        $qb->setParameter('now', new \DateTime('now'))
            ->where('n.publish_date <= :now')
            ->andWhere('n.visible = true')
            ->addOrderBy('n.publish_date', 'DESC')
            ->addOrderBy('n.id', 'DESC');

        return $qb;

    }
}
