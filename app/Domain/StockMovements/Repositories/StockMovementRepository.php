<?php

namespace App\Domain\StockMovements\Repositories;

use App\Domain\Branch\Services\BranchScope;
use App\Models\StockMovement;
use App\Support\Query\BaseRepository;
use Illuminate\Support\Facades\Auth;

class StockMovementRepository extends BaseRepository
{
    public function __construct(
        private readonly BranchScope $branchScope,
    ) {}

    protected string $model = StockMovement::class;

    protected array $relations = [
        'item',
        'branch',
        'batch',
        'performer',
    ];

    protected array $searchable = [
        'reason',
        'item.name',
        'item.sku',
    ];

    protected array $filterable = [
        'branch_id'    => 'exact',
        'item_id'      => 'exact',
        'type'         => 'exact',
        'performed_by' => 'exact',
        'created_at'   => 'date_range',
    ];

    protected array $sortable = [
        'id',
        'created_at',
        'quantity_delta',
    ];

    // Newest first, by id rather than created_at: several movements can share a
    // timestamp to the second (one withdrawal spanning three lots), and only the
    // id preserves the order they were actually applied in.
    protected string $defaultOrderBy = 'id';
    protected string $defaultOrderDirection = 'desc';

    /**
     * The ledger is scoped exactly like the stock it describes — otherwise it
     * would be a side door onto another branch's consumption history.
     */
    public function paginate(array $params = [], ?string $resourceClass = null): array
    {
        $query = $this->query();

        if ($user = Auth::user()) {
            $this->branchScope->apply($query, $user);
        }

        return $this->paginateQuery($query, $params, $resourceClass);
    }
}
