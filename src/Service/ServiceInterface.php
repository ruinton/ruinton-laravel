<?php


namespace Ruinton\Service;

use Ruinton\Enums\MediaTypes;
use Ruinton\Parser\QueryParam;

interface ServiceInterface
{
    public function all(?QueryParam $queryParam = null, bool $pagination = true) : ServiceResult;

    public function find($id = null, ?QueryParam $queryParam = null) : ServiceResult;

    public function create(array $data, ?QueryParam $queryParam = null) : ServiceResult;

    public function update($id, array $data, ?QueryParam $queryParam = null) : ServiceResult;

    public function delete($id, ?QueryParam $queryParam = null) : ServiceResult;

    public function bulkUpdate(array $data) : ServiceResult;

    public function bulkUpdateOrInsert(array $data) : ServiceResult;

    public function bulkDelete(array $data) : ServiceResult;

    public function createMedia(array $files) : ServiceResult;

    public function deleteMedia(int $id = 0) : ServiceResult;

    public function swapPriority(int $fromId = 0, int $toId = 0) : ServiceResult;
}
