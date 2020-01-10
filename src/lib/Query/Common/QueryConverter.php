<?php
/**
 * @copyright Novactive
 * Date: 10/01/2020
 */

declare(strict_types=1);

namespace Novactive\EzSolrSearchExtra\Query\Common;

use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformSolrSearchEngine\Query\CriterionVisitor;
use EzSystems\EzPlatformSolrSearchEngine\Query\QueryConverter as BaseQueryConverter;

class QueryConverter extends BaseQueryConverter
{
    /** @var BaseQueryConverter */
    protected $baseConverter;

    /** @var CriterionVisitor */
    protected $criterionVisitor;

    /**
     * QueryConverter constructor.
     */
    public function __construct(BaseQueryConverter $baseConverter, CriterionVisitor $criterionVisitor)
    {
        $this->baseConverter    = $baseConverter;
        $this->criterionVisitor = $criterionVisitor;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(Query $query)
    {
        $params = $this->baseConverter->convert($query);

        if ($query->filter instanceof Query\Criterion\LogicalAnd) {
            $params['fq'] = [];
            foreach ($query->filter->criteria as $criterion) {
                if ($criterion instanceof Query\Criterion\LogicalAnd) {
                    foreach ($criterion->criteria as $subcriterion) {
                        $params['fq'][] = $this->criterionVisitor->visit($subcriterion);
                    }
                } else {
                    $params['fq'][] = $this->criterionVisitor->visit($criterion);
                }
            }
        }

        return $params;
    }
}