<?php declare(strict_types=1);

namespace Leko\LaravelPublished\Traits;

use Carbon\Carbon;
use Closure;
use Leko\LaravelPublished\Scopes\PublicationScope;

/**
 * Trait for publish entity.
 */
trait PublishedTrait
{
    /**
     * Boot the published trait for a model.
     *
     * @return void
     */
    public static function bootPublishedTrait()
    {
        static::addGlobalScope(new PublicationScope);
    }

    /**
     * Perform the actual publish query on this model instance.
     *
     * @return void
     */
    public function publish(Carbon $time = null)
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        if (is_null($time)) {
            $time = $this->freshTimestamp();
        }

        $columns = [$this->getPublishedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getPublishedAtColumn()} = $time;

        if ($this->usesTimestamps() && !is_null($this->getPublishedAtColumn())) {
            $this->{$this->getPublishedAtColumn()} = $time;

            $columns[$this->getPublishedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));

        $this->fireModelEvent('published', false);
    }

    /**
     * Unpublish a published model instance.
     *
     * @return bool
     */
    public function unpublish()
    {
        if ($this->fireModelEvent('unpublishing') === false) {
            return false;
        }

        $this->{$this->getPublishedAtColumn()} = null;

        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('unpublished', false);

        return $result;
    }

    /**
     * Publish model without raising any events.
     *
     * @return bool|null
     */
    public function publishQuietly(): ?bool
    {
        return static::withoutEvents(fn () => $this->publish());
    }

    /**
     * Restore a publishing model instance without raising any events.
     *
     * @return bool
     */
    public function unpublishQuietly(): bool
    {
        return static::withoutEvents(fn () => $this->unpublish());
    }

    /**
     * Determine if the model instance has been published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        $time = $this->freshTimestamp();
        return !is_null($this->{$this->getPublishedAtColumn()}) && $this->{$this->getPublishedAtColumn()} <= $time;
    }

    /**
     * Register a “published” model event callback with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function published($callback): void
    {
        static::registerModelEvent('published', $callback);
    }

    /**
     * Register a “unpublished” model event callback with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function unpublished($callback): void
    {
        static::registerModelEvent('unpublished', $callback);
    }

    /**
     * Get the name of the “published at” column.
     *
     * @return string
     */
    public function getPublishedAtColumn(): string
    {
        return defined(static::class . '::PUBLISHED_AT') ? static::PUBLISHED_AT : 'published_at';
    }

    /**
     * Get the fully qualified “published at” column.
     *
     * @return string
     */
    public function getQualifiedPublishedAtColumn(): string
    {
        return $this->qualifyColumn($this->getPublishedAtColumn());
    }
}