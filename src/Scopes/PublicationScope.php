<?php

namespace Leko\LaravelPublished\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class of the scopes for a builder.
 */
class PublicationScope implements Scope
{
    /**
     * All the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['Unpublished', 'WithUnpublished', 'WithoutPublished'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getQualifiedPublishedAtColumn(), '<=', $model->fromDateTime($model->freshTimestamp()));
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param Builder $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the unpublished extension to the builder.
     *
     * @param Builder $builder
     * @return void
     */
    protected function addUnpublished(Builder $builder): void
    {
        $builder->macro('unpublished', function (Builder $builder) {
            $model = $builder->getModel();

            return $builder->withoutGlobalScope($this)
                ->whereNull($model->getQualifiedPublishedAtColumn())
                ->orWhere($model->getQualifiedPublishedAtColumn(), '>', $model->fromDateTime($model->freshTimestamp()));
        });
    }

    /**
     * Add the with-unpublished extension to the builder.
     *
     * @param Builder $builder
     * @return void
     */
    protected function addWithUnpublished(Builder $builder): void
    {
        $builder->macro('withUnpublished', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-published extension to the builder.
     *
     * @param Builder $builder
     * @return void
     */
    protected function addWithoutPublished(Builder $builder): void
    {
        $builder->macro('withoutPublished', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNull(
                $model->getQualifiedPublishedAtColumn()
            );

            return $builder;
        });
    }
}
