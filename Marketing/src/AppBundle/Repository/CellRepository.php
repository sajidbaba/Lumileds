<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Cell;
use AppBundle\Entity\ContributionCountryRequest;
use AppBundle\Entity\ContributionRequest;
use AppBundle\Entity\Country;
use AppBundle\Entity\Indicator;
use AppBundle\Entity\Order;
use AppBundle\Entity\Region;
use AppBundle\Entity\Segment;
use AppBundle\Entity\Technology;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;

class CellRepository extends EntityRepository
{
    private const SIGNALING_TECHNOLOGIES = [
        Technology::TECHNOLOGY_SL_CONV,
        Technology::TECHNOLOGY_SL_LED_RF,
        Technology::TECHNOLOGY_SL_HIPER,
    ];

    private const HALOGEN_TECHNOLOGIES = [
        Technology::TECHNOLOGY_HL_HALOGEN,
        Technology::TECHNOLOGY_HL_NON_HALOGEN,
    ];

    /**
     * Fetches a cell entity based on certain criteria.
     *
     * @param Country $market Country entity or country id.
     * @param Segment $segment Segment entity or segment id.
     * @param Indicator|int $indicator Indicator id.
     * @param Technology|int|null $technology Technology entity or technology id.
     * @param string $year Year value.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Cell|null Matched cell, if any.
     */
    public function findOneByIndicatorTechnologyAndYear(
        $market,
        $segment,
        $indicator,
        $technology,
        $year
    ): ?Cell {
        return $this->createQueryBuilder('c')
            ->select('c,e')
            ->leftJoin('c.errorLog', 'e')
            ->where('c.country = :country')
            ->andWhere('c.segment = :segment')
            ->andWhere('c.indicator = :indicator')
            ->andWhere('c.technology = :technology OR c.technology IS NULL')
            ->andWhere('c.year = :year')
            ->setParameters([
                'country' => $market,
                'segment' => $segment,
                'indicator' => $indicator,
                'technology' => $technology,
                'year' => $year,
            ])
            ->orderBy('c.technology', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->setCacheable(true)
            ->getOneOrNullResult();
    }

    /**
     * Fetches cell entities based on certain criteria.
     *
     * @param Country $market Country entity or country id.
     * @param Segment $segment Segment entity or segment id.
     * @param array $indicators Indicator ids.
     * @param array $technologies Technologies.
     * @param string $year Year value.
     *
     * @return Cell[]
     */
    public function findByIndicatorTechnologyAndYear(
        $market,
        $segment,
        array $indicators,
        array $technologies,
        $year
    ) {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->select('c,e')
            ->leftJoin('c.errorLog', 'e')
            ->where('c.country = :country')
            ->andWhere('c.segment = :segment')
            ->andWhere('c.year = :year')
            ->andWhere($qb->expr()->in('c.indicator', $indicators))
            ->andWhere($qb->expr()->in('c.technology', $technologies))
            ->setParameter('country', $market)
            ->setParameter('segment', $segment)
            ->setParameter('year', $year)
            ->getQuery()
            ->setCacheable(true)
            ->getResult();
    }

    /**
     * Get all cells ordered by combination of indicator-technology and year
     * Filtered by indicators, market, or segment
     *
     * @param array $filters
     *
     * @return Cell[]
     */
    public function findAllFiltered(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, e, t, i')
            ->leftJoin('c.errorLog', 'e')
            ->leftJoin('c.technology', 't')
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.indicator', 'i')
            ->leftJoin('c.country', 'co')
            ->leftJoin(
                Order::class,
                'o',
                Join::WITH,
                'c.indicator = o.indicator AND
                (c.technology = o.technology OR
                (c.technology IS NULL AND o.technology IS NULL))'
            )
            ->leftJoin('co.region', 'r')
            ->where('co.active = 1')
            ->orderBy('s.id, co.id, c.segment, o.priority, c.year');

        $this->addFiltersToQb($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get all cells for current version
     *
     * @param int $id
     * @param array $filters
     *
     * @return Cell[]
     */
    public function findVersionCells(int $id, array $filters)
    {
        $rsm = (new ResultSetMapping())
            ->addEntityResult(Cell::class, 'c')
            ->addFieldResult('c', 'id', 'id')
            ->addFieldResult('c', 'value', 'value')
            ->addFieldResult('c', 'year', 'year');

        $rsm->addJoinedEntityResult(Country::class, 'co', 'c', 'country')
            ->addFieldResult('co', 'country_id', 'id')
            ->addFieldResult('co', 'country_name', 'name');

        $rsm->addJoinedEntityResult(
            ContributionCountryRequest::class,
            'ccr',
            'co',
            'contributionCountryRequest'
        );

        $rsm->addJoinedEntityResult(Region::class, 'r', 'co', 'region')
            ->addFieldResult('r', 'region_id', 'id')
            ->addFieldResult('r', 'region_name', 'name');

        $rsm->addJoinedEntityResult(
            ContributionRequest::class,
            'cr',
            'r',
            'contributionRequest'
        );

        $rsm->addJoinedEntityResult(Segment::class, 's', 'c', 'segment')
            ->addFieldResult('s', 'segment_id', 'id')
            ->addFieldResult('s', 'segment_name', 'name');

        $rsm->addJoinedEntityResult(Indicator::class, 'i', 'c', 'indicator')
            ->addFieldResult('i', 'indicator_id', 'id')
            ->addFieldResult('i', 'indicator_name', 'name');

        $rsm->addJoinedEntityResult(Technology::class, 't', 'c', 'technology')
            ->addFieldResult('t', 'technology_id', 'id')
            ->addFieldResult('t', 'technology_name', 'name');

        $rsm->addScalarResult('version', 'version');

        list($params, $filters) = $this->buildFiltersSQL($filters);

        $sql = '
            SELECT 
              c.id,
              c.year,
              IF (v2.value, v2.value, c.value) AS value,
              co.id                            AS country_id,
              co.name                          AS country_name,
              r.id                             AS region_id,
              r.name                           AS region_name,
              s.id                             AS segment_id,
              s.name                           AS segment_name,
              i.id                             AS indicator_id,
              i.name                           AS indicator_name,
              t.id                             AS technology_id,
              t.name                           AS technology_name,
              v1.value                         AS version
            FROM cells c
            LEFT JOIN countries co                  ON c.country_id = co.id
            LEFT JOIN regions r                     ON co.region_id = r.id
            LEFT JOIN contribution_country_requests ON co.id = contribution_country_requests.country_id
            LEFT JOIN segments s                    ON c.segment_id = s.id
            LEFT JOIN indicators i                  ON c.indicator_id = i.id
            LEFT JOIN technologies t                ON c.technology_id = t.id
            LEFT JOIN cell_versions v1              ON c.id = v1.cell_id AND v1.version_id = :version
            LEFT JOIN (
                SELECT f.cell_id, f.value, f.version_id
                FROM (
                    SELECT cell_id, MIN(version_id) AS min_version
                    FROM cell_versions
                    WHERE version_id > :version
                    GROUP BY cell_id
                ) AS x INNER JOIN cell_versions AS f ON f.cell_id = x.cell_id and f.version_id = x.min_version
            ) AS v2 ON c.id = v2.cell_id
            LEFT JOIN orders o ON (
                c.indicator_id = o.indicator_id AND 
                (c.technology_id = o.technology_id OR (c.technology_id IS NULL AND o.technology_id IS NULL))
            )';

        if ($filters === ' ') {
            $filters = 'WHERE co.active = 1';
        } else {
            $filters .= 'AND (co.active = 1)';
        }

        $sql .= $filters;
        $sql .= ' ORDER BY co.id, c.segment_id, o.priority ASC, c.year ASC';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        $params['version'] = $id;
        $query->setParameters($params);

        $result = $query->getResult();

        return $result;
    }

    /**
     * @param Cell $cell
     * @param int $diff
     *
     * @return Cell|null
     */
    public function findAdjoiningCell(Cell $cell, int $diff): ?Cell
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c,e')
            ->leftJoin('c.errorLog', 'e')
            ->where('c.country = :country')
            ->andWhere('c.segment = :segment')
            ->andWhere('c.indicator = :indicator')
            ->andWhere('c.year = :year')
            ->setParameter('country', $cell->getCountry())
            ->setParameter('segment', $cell->getSegment())
            ->setParameter('indicator', $cell->getIndicator())
            ->setParameter('year', $cell->getYear() + $diff);

        if ($cell->getTechnology()) {
            $qb->andWhere('c.technology = :technology')
                ->setParameter('technology', $cell->getTechnology());
        } else {
            $qb->andWhere('c.technology IS NULL');
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Build SQL from filters
     *
     * @param array $filters
     *
     * @return array
     */
    private function buildFiltersSQL(array $filters): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['markets'])) {
            $options = [];
            foreach ($filters['markets'] as $id => $marketId) {
                $key = 'market_'.$id;
                $options[] = 'co.id = :'.$key;
                $params[$key] = $marketId;
            }

            if ($options) {
                $conditions[] = implode(' OR ', $options);
            }
        }

        if (!empty($filters['segment'])) {
            $conditions[] = 's.name = :segment';
            $params['segment'] = $filters['segment'];
        } elseif (!empty($filters['segments'])) {
            $options = [];
            foreach ($filters['segments'] as $id => $segmentId) {
                $key = 'segment_'.$id;
                $options[] = 's.name = :'.$key;
                $params[$key] = $segmentId;
            }

            if ($options) {
                $conditions[] = implode(' OR ', $options);
            }
        }

        if (!empty($filters['regions'])) {
            $options = [];
            foreach ($filters['regions'] as $id => $regionId) {
                $key = 'region_'.$id;
                $options[] = 'r.id = :'.$key;
                $params[$key] = $regionId;
            }

            if ($options) {
                $conditions[] = implode(' OR ', $options);
            }
        }

        if (!empty($filters['indicators'])) {
            $options = [];
            foreach ($filters['indicators'] as $id => $indicatorId) {
                $key = 'indicator_'.$id;
                $options[] = 'i.id = :'.$key;
                $params[$key] = $indicatorId;
            }

            if ($options) {
                $conditions[] = implode(' OR ', $options);
            }
        }

        if (!empty($filters['years'])) {
            $options = [];
            foreach ($filters['years'] as $id => $indicatorId) {
                $key = 'year_'.$id;
                $options[] = 'c.year = :'.$key;
                $params[$key] = $indicatorId;
            }

            if ($options) {
                $conditions[] = implode(' OR ', $options);
            }
        }

        if (count($conditions) > 0) {
            $conditions = array_map(function ($condition) {
                return '('.$condition.')';
            }, $conditions);

            $filters = 'WHERE '.implode(' AND ', $conditions).' ';
        } else {
            $filters = ' ';
        }

        return [$params, $filters];
    }

    /**
     * @param $qb QueryBuilder
     * @param $filters
     */
    public function addFiltersToQb(QueryBuilder $qb, array $filters): void
    {
        $conditions = [];
        if (!empty($filters['markets'])) {
            foreach ($filters['markets'] as $marketId) {
                $conditions[] = $qb->expr()->eq('c.country', $marketId);
            }

            $orX = $qb->expr()->orX();
            $orX->addMultiple($conditions);
            $qb->andWhere($orX);
        }

        if (!empty($filters['segments'])) {
            $conditions = [];
            foreach ($filters['segments'] as $id => $segment) {
                $conditions[] = $qb->expr()->eq('s.name', ':segment_'.$id);
                $qb->setParameter('segment_'.$id, $segment);
            }

            $orX = $qb->expr()->orX();
            $orX->addMultiple($conditions);
            $qb->andWhere($orX);
        }

        if (!empty($filters['segment'])) {
            $qb->andWhere('s.name = :segment');
            $qb->setParameter('segment', $filters['segment']);
        }

        if (!empty($filters['regions'])) {
            $conditions = [];
            foreach ($filters['regions'] as $regionId) {
                $conditions[] = $qb->expr()->eq('co.region', $regionId);
            }

            $orX = $qb->expr()->orX();
            $orX->addMultiple($conditions);
            $qb->andWhere($orX);
        }

        if (!empty($filters['years'])) {
            $conditions = [];
            foreach ($filters['years'] as $year) {
                $conditions[] = $qb->expr()->eq('c.year', $year);
            }

            $orX = $qb->expr()->orX();
            $orX->addMultiple($conditions);
            $qb->andWhere($orX);
        }

        if (!empty($filters['indicators'])) {
            $conditions = [];
            foreach ($filters['indicators'] as $indicatorId) {
                $conditions[] = $qb->expr()->eq('c.indicator', $indicatorId);
            }

            $orX = $qb->expr()->orX();
            $orX->addMultiple($conditions);
            $qb->andWhere($orX);
        }

        if (!empty($filters['technologies'])) {
            $conditions = [];
            foreach ($filters['technologies'] as $technologyId) {
                if ($technologyId === null) {
                    $conditions[] = $qb->expr()->isNull('c.technology');
                } else {
                    $conditions[] = $qb->expr()->eq('c.technology', $technologyId);
                }
            }

            $orX = $qb->expr()->orX();
            $orX->addMultiple($conditions);
            $qb->andWhere($orX);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     */
    private function addTechnologySetFilterToQb(QueryBuilder $qb, array $filters): void
    {
        if (!empty($filters['technologySet'])) {
            if ($filters['technologySet'] === 'hl') {
                $qb->andWhere(
                    $qb->expr()->in(
                        'c.technology',
                        [
                            Technology::TECHNOLOGY_HL_HALOGEN,
                            Technology::TECHNOLOGY_HL_XENON,
                            Technology::TECHNOLOGY_HL_LED_RF,
                            Technology::TECHNOLOGY_HL_NON_HALOGEN,
                        ]
                    )
                );
            } elseif ($filters['technologySet'] === 'sl') {
                $qb->andWhere(
                    $qb->expr()->in(
                        'c.technology',
                        [
                            Technology::TECHNOLOGY_SL_CONV,
                            Technology::TECHNOLOGY_SL_HIPER,
                            Technology::TECHNOLOGY_SL_LED_RF,
                        ]
                    )
                );
            } elseif ($filters['technologySet'] === 'lamps') {
                $qb->andWhere(
                    $qb->expr()->in(
                        'c.technology',
                        [
                            Technology::TECHNOLOGY_HL_HALOGEN,
                            Technology::TECHNOLOGY_HL_XENON,
                            Technology::TECHNOLOGY_HL_NON_HALOGEN,
                            Technology::TECHNOLOGY_SL_CONV,
                            Technology::TECHNOLOGY_SL_HIPER,
                        ]
                    )
                );
            } elseif ($filters['technologySet'] === 'led') {
                $qb->andWhere(
                    $qb->expr()->in(
                        'c.technology',
                        [
                            Technology::TECHNOLOGY_HL_LED_RF,
                            Technology::TECHNOLOGY_SL_LED_RF,
                        ]
                    )
                );
            }
        }
    }

    /**
     * Get Parc by segment
     *
     * @param array $filters
     *
     * @return array
     */
    public function getParcBySegment(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('SUM(c.value), s.name, c.year')
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.indicator', 'i')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->where('i = :indicatorId')
            ->andWhere('co.active = 1')
            ->groupBy('s.name, c.year')
            ->orderBy('s.id, c.year')
            ->setParameter('indicatorId', Indicator::INDICATOR_PARC);

        $this->addFiltersToQb($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get Parc by segment
     *
     * @param array $filters
     *
     * @return array
     */
    public function getSimpleIndicatorChartData(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id, c.value, c.year')
            ->indexBy('c', 'c.id')
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.indicator', 'i')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->where('i = :indicatorId')
            ->andWhere('co.active = 1')
            ->orderBy('c.year')
            ->setParameter('indicatorId', $filters['indicator']);

        if (
            $filters['indicator'] === Indicator::INDICATOR_MARKET_VOLUME ||
            $filters['indicator'] === Indicator::INDICATOR_MARKET_VALUE_USD
        ) {
            $qb->leftJoin('c.technology', 't')
                ->andWhere('t.id <> :totalTechnology')
                ->setParameter('totalTechnology', Technology::TECHNOLOGY_TOTAL);
        }

        $this->addFiltersToQb($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get Parc by region
     *
     * @param array $filters
     *
     * @return array
     */
    public function getParcByRegion(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('SUM(c.value), r.name, c.year')
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.indicator', 'i')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->where('i = :indicatorId')
            ->andWhere('co.region IS NOT NULL')
            ->andWhere('co.active = 1')
            ->groupBy('r.name, c.year')
            ->setParameter('indicatorId', Indicator::INDICATOR_PARC);

        $this->addFiltersToQb($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get Parc by technology split
     *
     * @param array $filters
     *
     * @return array
     */
    public function getParcByTechnology(array $filters): array
    {
        list($params, $filters) = $this->buildFiltersSQL($filters);

        if ($filters === ' ') {
            $filters = ' WHERE';
        } else {
            $filters .= ' AND';
        }
        $filters .= ' c.indicator_id = :parc_indicator AND co.active = 1';

        $sql = '
            SELECT
              SUM(c.value * tech_split.value) / c2.value AS value,
              c.year,
              t.id    AS techId,
              t.name  AS techName
            FROM cells c 
            LEFT JOIN cells tech_split ON 
                tech_split.indicator_id = 3 
                AND tech_split.technology_id IN (
                    :technology_non_halogen,
                    :technology_led,
                    :technology_led_rf,
                    :technology_xenon
                )
                AND c.segment_id = tech_split.segment_id 
                AND c.country_id = tech_split.country_id
                AND c.year = tech_split.year
            LEFT JOIN technologies t ON tech_split.technology_id = t.id
            LEFT JOIN segments s ON c.segment_id = s.id
            LEFT JOIN countries co ON c.country_id = co.id
            LEFT JOIN regions r ON co.region_id = r.id
            LEFT JOIN (
                SELECT SUM(value) AS value, year
                FROM cells AS c
                LEFT JOIN segments s ON c.segment_id = s.id
                LEFT JOIN countries co ON c.country_id = co.id
                LEFT JOIN regions r ON co.region_id = r.id
                '.$filters.'
                GROUP BY year
            ) as c2 ON c2.year = c.year
            '.$filters.'
            GROUP BY c.year, tech_split.technology_id;
        ';

        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->prepare($sql);

        $params['parc_indicator'] = Indicator::INDICATOR_PARC;
        $params['tech_split_indicator'] = Indicator::INDICATOR_TECH_SPLIT;
        $params['technology_non_halogen'] = Technology::TECHNOLOGY_HL_NON_HALOGEN;
        $params['technology_xenon'] = Technology::TECHNOLOGY_HL_XENON;
        $params['technology_led'] = Technology::TECHNOLOGY_HL_LED;
        $params['technology_led_rf'] = Technology::TECHNOLOGY_HL_LED_RF;

        $stmt->execute($params);
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * Get Market Volume by region
     *
     * @param array $filters
     *
     * @return array
     */
    public function getMarketVolumeByRegion(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select(
                'SUM(c.value) as value,
                c.year,
                r.name as region'
            )
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->leftJoin('c.technology', 't')
            ->where('c.indicator = :indicator')
            ->andWhere('co.region IS NOT NULL')
            ->andWhere('co.active = 1')
            ->andWhere('c.value IS NOT NULL')
            ->groupBy('r.name, c.year, r.id')
            ->setParameter('indicator', Indicator::INDICATOR_MARKET_VOLUME)
            ->setParameter('total_technology', Technology::TECHNOLOGY_TOTAL)
        ;

        $this->addFiltersToQb($qb, $filters);

        if (!empty($filters['technologySet'])) {
            $qb->andWhere('t.id <> :total_technology');

            $this->addTechnologySetFilterToQb($qb, $filters);
        } else {
            $qb->andWhere('c.technology = :total_technology');
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get Market Volume by segment
     *
     * @param array $filters
     *
     * @return array
     */
    public function getMarketVolumeBySegment(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select(
                'SUM(c.value) as value,
                c.year,
                s.name as segment'
            )
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->leftJoin('c.technology', 't')
            ->where('c.indicator = :indicator')
            ->andWhere('co.active = 1')
            ->andWhere('c.value IS NOT NULL')
            ->groupBy('c.year, s.id')
            ->setParameter('indicator', Indicator::INDICATOR_MARKET_VOLUME)
            ->setParameter('total_technology', Technology::TECHNOLOGY_TOTAL)
        ;

        $this->addFiltersToQb($qb, $filters);

        if (!empty($filters['technologySet'])) {
            $qb->andWhere('t.id <> :total_technology');

            $this->addTechnologySetFilterToQb($qb, $filters);
        } else {
            $qb->andWhere('c.technology = :total_technology');
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get Market Size by region
     *
     * @param array $filters
     *
     * @return array
     */
    public function getMarketSizeByRegion(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select(
                'SUM(c.value) as value,
                c.year,
                r.name as region'
            )
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->leftJoin('c.technology', 't')
            ->where('c.indicator = :indicator')
            ->andWhere('co.region IS NOT NULL')
            ->andWhere('co.active = 1')
            ->groupBy('r.name, c.year, r.id')
            ->setParameter('indicator', Indicator::INDICATOR_MARKET_VALUE_USD)
            ->setParameter('total_technology', Technology::TECHNOLOGY_TOTAL)
        ;

        $this->addFiltersToQb($qb, $filters);

        if (!empty($filters['technologySet'])) {
            $qb->andWhere('t.id <> :total_technology');

            $this->addTechnologySetFilterToQb($qb, $filters);
        } else {
            $qb->andWhere('c.technology = :total_technology');
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get Market Size by segment
     *
     * @param array $filters
     *
     * @return array
     */
    public function getMarketSizeBySegment(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select(
                'SUM(c.value) as value,
                c.year,
                s.name as segment'
            )
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->leftJoin('c.technology', 't')
            ->where('c.indicator = :indicator')
            ->andWhere('co.active = 1')
            ->groupBy('c.year, s.id')
            ->setParameter('indicator', Indicator::INDICATOR_MARKET_VALUE_USD)
            ->setParameter('total_technology', Technology::TECHNOLOGY_TOTAL)
        ;

        $this->addFiltersToQb($qb, $filters);

        if (!empty($filters['technologySet'])) {
            $qb->andWhere('t.id <> :total_technology');

            $this->addTechnologySetFilterToQb($qb, $filters);
        } else {
            $qb->andWhere('c.technology = :total_technology');
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get Market Volume by technology
     *
     * @param array $filters
     *
     * @return array
     */
    public function getMarketVolumeByTechnology(array $filters): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select(
                'SUM(c.value) as value,
                c.year,
                t.id as technology'
            )
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->leftJoin('c.technology', 't')
            ->where('c.indicator = :indicator')
            ->andWhere(
                $qb->expr()->notIn(
                    'c.technology',
                    [
                        Technology::TECHNOLOGY_TOTAL,
                    ]
                )
            )
            ->andWhere('co.active = 1')
            ->groupBy('c.year, t.id')
            ->orderBy('c.year')
            ->setParameter('indicator', Indicator::INDICATOR_MARKET_VOLUME)
        ;

        $this->addFiltersToQb($qb, $filters);
        $this->addTechnologySetFilterToQb($qb, $filters);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get Market Volume by technology
     *
     * @param array $filters
     *
     * @return array
     */
    public function getMarketSizeByTechnology(array $filters): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select(
            'SUM(c.value) as value,
                c.year,
                t.id as technology,
                t.name as technology_name'
        )
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->leftJoin('c.technology', 't')
            ->where('c.indicator = :indicator')
            ->andWhere(
                $qb->expr()->notIn(
                    'c.technology',
                    [
                        Technology::TECHNOLOGY_TOTAL,
                    ]
                )
            )
            ->andWhere('co.active = 1')
            ->groupBy('technology_name, c.year, t.id')
            ->orderBy('c.year')
            ->setParameter('indicator', Indicator::INDICATOR_MARKET_VALUE_USD)
        ;

        $this->addFiltersToQb($qb, $filters);
        $this->addTechnologySetFilterToQb($qb, $filters);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    public function getMarketShareByRegion(array $filters): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->select('r.name as region, c.year')
            ->addSelect('
                SumIf(i.id = :ll_sales_usd_indicator, c.value) as llSales,
                SumIf(i.id = :market_value_usd_indicator, c.value) as marketValue
            ')
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.indicator', 'i')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->leftJoin('c.technology', 't')
            ->where('i.id = :ll_sales_usd_indicator OR i.id = :market_value_usd_indicator')
            ->andWhere('t.id <> :total_technology')
            ->andWhere('co.region IS NOT NULL')
            ->andWhere('c.value IS NOT NULL')
            ->andWhere('co.active = 1')
            ->groupBy('r.id, c.year')
            ->setParameters([
                'll_sales_usd_indicator' => Indicator::INDICATOR_LL_SALES_USD,
                'market_value_usd_indicator' => Indicator::INDICATOR_MARKET_VALUE_USD,
                'total_technology' => Technology::TECHNOLOGY_TOTAL,
            ]);

        $this->addFiltersToQb($qb, $filters);
        $this->addTechnologySetFilterToQb($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    public function getMarketShareByTechnology(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('t.id as technology, c.year as year')
            ->addSelect('
                SumIf(i.id = :ll_sales_usd_indicator, c.value) /
                SumIf(i.id = :market_value_usd_indicator, c.value) as value,
                SumIf(i.id = :ll_sales_usd_indicator, c.value) as llSales, 
                SumIf(i.id = :market_value_usd_indicator, c.value) as marketValue
            ')
            ->leftJoin('c.segment', 's')
            ->leftJoin('c.indicator', 'i')
            ->leftJoin('c.country', 'co')
            ->leftJoin('co.region', 'r')
            ->leftJoin('c.technology', 't')
            ->where('i.id = :ll_sales_usd_indicator OR i.id = :market_value_usd_indicator')
            ->andWhere('t.id <> :total_technology')
            ->andWhere('co.active = 1')
            ->groupBy('t.id, c.year')
            ->setParameters([
                'll_sales_usd_indicator' => Indicator::INDICATOR_LL_SALES_USD,
                'market_value_usd_indicator' => Indicator::INDICATOR_MARKET_VALUE_USD,
                'total_technology' => Technology::TECHNOLOGY_TOTAL,
            ]);

        $this->addFiltersToQb($qb, $filters);
        $this->addTechnologySetFilterToQb($qb, $filters);

        $data = $qb->getQuery()->getResult();

        $data = $this->groupDataByTechnologiesForMarketShare($data, self::HALOGEN_TECHNOLOGIES, Technology::TECHNOLOGY_HL_HALOGEN);

        return $data;
    }

    /**
     * @param array $data
     * @param array $searchTechnologies
     * @param int $mergedTechnology
     *
     * @return array
     */
    private function groupDataByTechnologiesForMarketShare(array $data, array $searchTechnologies, int $mergedTechnology): array
    {
        $groupedValues = [];

        foreach ($data as $key => $field) {
            $year = $field['year'];
            $technology = $field['technology'];

            if (!in_array($technology, $searchTechnologies)) {
                continue;
            }

            if (!array_key_exists($year, $groupedValues)) {
                $groupedValues[$year] = ['value' => 0, 'llSales' => 0, 'marketValue' => 0];
            }

            $llSales = array_key_exists('llSales', $field) ? $field['llSales'] : 0;
            $marketValue = array_key_exists('marketValue', $field) ? $field['marketValue'] : 0;

            $groupedValues[$year]['llSales'] += (float)$llSales;
            $groupedValues[$year]['marketValue'] += (float)$marketValue;

            unset($data[$key]);
        }

        foreach ($groupedValues as $year => $value) {
            $data[] = [
                'value' => $value['llSales'] / $value['marketValue'],
                'llSales' => $value['llSales'],
                'marketValue' => $value['marketValue'],
                'year' => $year,
                'technology' => $mergedTechnology,
            ];
        }

        return array_values($data);
    }

    /**
     * @param array $ids
     *
     * @return Cell[]
     */
    public function findByIdsWithContributions(array $ids): array
    {
        return $this->createQueryBuilder('c')
            ->select('c, ccm')
            ->leftJoin('c.contributionCellModifications', 'ccm')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}
