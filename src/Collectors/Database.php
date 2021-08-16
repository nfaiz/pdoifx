<?php

namespace Nfaiz\PdoIfx\Collectors;

use CodeIgniter\Debug\Toolbar\Collectors\BaseCollector;
use Nfaiz\DbToolbar\Toolbar;

class Database extends BaseCollector
{
    /**
     * Whether this collector has data that can
     * be displayed in the Timeline.
     *
     * @var boolean
     */
    protected $hasTimeline = true;

    /**
     * Whether this collector needs to display
     * content in a tab or not.
     *
     * @var boolean
     */
    protected $hasTabContent = true;

    /**
     * Whether this collector has data that
     * should be shown in the Vars tab.
     *
     * @var boolean
     */
    protected $hasVarData = false;

    /**
     * The 'title' of this Collector.
     * Used to name things in the toolbar HTML.
     *
     * @var string
     */
    protected $title = 'Queries';

    /**
     * The query instances that have been collected
     * through the PdoIfx Event.
     *
     * @var Query[]
     */
    protected static $queries = [];


    public function __construct()
    {
        $config = config(PdoIfx::class);

        $this->title = $config->tabTitle;
    }

    public static function collect(array $query)
    {
        $config = config(Toolbar::class);

        // Provide default in case it's not set
        $max = $config->maxQueries ?: 100;

        if (count(static::$queries) < $max) {
            static::$queries[] = $query;
        }
    }

    /**
     * Returns timeline data formatted for the toolbar.
     *
     * @return array The formatted data or an empty array.
     */
    protected function formatTimelineData(): array
    {
        $data = [];

        $instance = '';

        foreach (static::$queries as $query)
        {
            if ($instance != $query['instance'])
            {
                $data[] = [
                    'name'      => 'Connecting to Database: ' . $query['instance'],
                    'component' => 'Database',
                    'start'     =>  $query['connectTime'],
                    'duration'  =>  $query['connectDuration'],
                ];
            }

            $instance = $query['instance'];
        }

        foreach (static::$queries as $query)
        {
            $data[] = [
                'name'      => 'Query',
                'component' => 'Database',
                'start'     => $query['start'],
                'duration'  => (float) number_format(($query['end'] - $query['start']), 6),
            ];
        }

        return $data;
    }

    public function display(): string
    {
        $toolbar = new Toolbar(static::$queries);

        return $toolbar->display('Nfaiz\PdoIfx\Views\queries.tpl');
    }

    /**
     * Gets the "badge" value for the button.
     *
     * @return int
     */
    public function getBadgeValue(): int
    {
        return count(static::$queries) ?: 0;
    }

    /**
     * Information to be displayed next to the title.
     *
     * @return string The number of queries (in parentheses) or an empty string.
     */
    public function getTitleDetails(): string
    {
        return  '(' . count(static::$queries) . ' Queries'  . ')';
    }

    /**
     * Display the icon.
     *
     * Icon from https://icons8.com - 1em package
     *
     * @return string
     */
    public function icon(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAADMSURBVEhLY6A3YExLSwsA4nIycQDIDIhRWEBqamo/UNF/SjDQjF6ocZgAKPkRiFeEhoYyQ4WIBiA9QAuWAPEHqBAmgLqgHcolGQD1V4DMgHIxwbCxYD+QBqcKINseKo6eWrBioPrtQBq/BcgY5ht0cUIYbBg2AJKkRxCNWkDQgtFUNJwtABr+F6igE8olGQD114HMgHIxAVDyAhA/AlpSA8RYUwoeXAPVex5qHCbIyMgwBCkAuQJIY00huDBUz/mUlBQDqHGjgBjAwAAACexpph6oHSQAAAAASUVORK5CYII=';
    }
}