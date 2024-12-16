<?php

namespace App\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;

/**
 * Repository trait
 */
trait Repository
{
    /**
     * When the called method doesn't exists on the Repository,
     * Call it on the model
     */
    public function __call($method, $parameters)
    {
        return $this->model->$method(...$parameters);
    }

    /**
     * Get all models.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Get model by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id, $relationships = null)
    {
        $query = $this->model->where('id', $id);

        if ($relationships != null && is_array($relationships)) {
            $query->with($relationships);
        }
        
        $model = $query->first();

        if (!$model) {
            $name = explode("\\", get_class($this->model));
            throw new ModelNotFoundException(end($name) . ' not found.');
        }

        return $model;
    }

    /**
     * Create a new model
     *
     * @param array $data
     * @return bool
     */
    public function create($data)
    {
        $model = $this->model->newInstance($data);

        $model->save();

        return $model->fresh();
    }

    /**
     * Update model by id or instance
     *
     * @param mixed $model
     * @param array $data
     * @return bool
     */
    public function update($model, $data)
    {
        if (!is_subclass_of($model, Model::class)) {
            $model = $this->getById($model);

            if (!$model) {
                throw new ModelNotFoundException();
            }
        }

        return $model->update($data);
    }

    /**
     * Delete model by id or instance
     *
     * @param mixed $model
     * @return mixed
     */
    public function delete($model)
    {
        if (!is_subclass_of($model, Model::class)) {
            $model = $this->getById($model);

            if (!$model) {
                throw new ModelNotFoundException();
            }
        }
        return $model->delete();
    }
}
