<?php

namespace App\Support\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected string $model;

    protected array $relations = [];
    protected array $searchable = [];
    protected array $filterable = [];
    protected array $sortable = [];
    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    public function query(): Builder
    {
        return $this->model::query()
            ->with($this->relations);
    }

    public function create(array $data): Model
    {
        return $this->model::create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model;
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function find(string|int $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function updateOrCreate(array $search, array $data): Model
    {
        return $this->model::updateOrCreate($search, $data);
    }

    public function paginate(array $params = [], ?string $resourceClass = null): array
    {
        return $this->paginateQuery(
            $this->query(),
            $params,
            $resourceClass
        );
    }

    public function applyQuery(Builder $query, array $params = []): Builder
    {
        $query = BaseQueryApplier::apply(
            query: $query,
            params: $params,
            searchable: $this->searchable,
            sortable: $this->sortable,
            filterable: $this->filterable
        );

        $this->applyDefaultSort($query, $params);

        return $query;
    }

    public function paginateQuery(
        Builder $query,
        array $params = [],
        ?string $resourceClass = null
    ): array {
        $query = $this->applyQuery($query, $params);

        $limit = $this->resolveLimit($params);
        $offset = $this->resolveOffset($params);
        $page = $this->resolvePage($offset, $limit);

        $paginator = $query->paginate(
            perPage: $limit,
            columns: ['*'],
            pageName: 'page',
            page: $page
        );

        $items = $paginator->items();

        return [
            'records' => $resourceClass && ! empty($items)
                ? $resourceClass::collection($items)
                : $items,

            'total' => $paginator->total(),
            'offset' => $offset,
            'limit' => $limit,

            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'has_more' => $paginator->hasMorePages(),
        ];
    }

    protected function applyDefaultSort(Builder $query, array $params): void
    {
        if (! empty($params['order_by'])) {
            return;
        }

        $query->orderBy($this->defaultOrderBy, $this->defaultOrderDirection);
    }

    protected function resolveLimit(array $params): int
    {
        return min((int) ($params['limit'] ?? 20), 100);
    }

    protected function resolveOffset(array $params): int
    {
        return max((int) ($params['offset'] ?? 0), 0);
    }

    protected function resolvePage(int $offset, int $limit): int
    {
        return $offset > 0
            ? (int) floor($offset / $limit) + 1
            : 1;
    }
}
