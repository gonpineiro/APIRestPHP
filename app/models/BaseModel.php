<?php

namespace App\Models;

use ErrorException;

use App\Connections\BaseDatos;

class BaseModel
{
    protected $table;
    protected $softDeleted = false;
    public $value;

    /** Metodos que se deben volver a ejecutar en un metodo de tipo list */
    protected $reExectMethods = [];

    /** Metodos que no se deben ejecutar, en los metodos de las relaciones */
    protected $filterMethod = [
        "__construct",
        "set",
        "list",
        "get",
        "save",
        "update",
        "delete",
        "hasOne",
        "executeSqlQuery",
        "filterMethods",
        "addFilterMethod"
    ];

    public function list($param = [], $ops = [])
    {
        $conn = new BaseDatos();
        $result = $conn->search($this->table, $param, $ops);

        if (!$result instanceof ErrorException) {
            $data = [];
            while ($row = odbc_fetch_array($result)) $data[] = $row;
            $this->value = $data;

            /* Ejecutamos los metodos para obtener las relaciones */
            $methods = $this->filterMethods(get_class_methods($this));
            foreach ($methods as $method) $this->$method();
        } else {
            logFileEE($this->logPath, $result, get_class($this), __FUNCTION__);
            $this->value = $result;
        }
        return $this;
    }

    public function get($params)
    {
        $conn = new BaseDatos();
        $result = $conn->search($this->table, $params);

        if (!$result instanceof ErrorException) {
            $this->value = $conn->fetch_assoc($result);

            /* Ejecutamos los metodos para obtener las relaciones */
            if ($this->value) {
                $methods = $this->filterMethods(get_class_methods($this));
                foreach ($methods as $method) $this->$method();
            }
        } else {
            logFileEE($this->logPath, $result, get_class($this), __FUNCTION__);
            $this->value = $result;
        }
        return $this;
    }

    public function save()
    {
        $array = json_decode(json_encode($this), true);
        $conn = new BaseDatos();
        $result = $conn->store($this->table, $array);

        if ($result instanceof ErrorException) {
            logFileEE($this->logPath, $result, get_class($this), __FUNCTION__);
        }
        return $result;
    }

    public function update($req, $id)
    {
        unset($req[$this->identity]);

        $conn = new BaseDatos();
        $result = $conn->update($this->table, $req, $id, $this->identity);

        if ($result instanceof ErrorException) {
            logFileEE($this->logPath, $result, get_class($this), __FUNCTION__);
        }

        return $result;
    }

    public function delete($id)
    {
        $conn = new BaseDatos();

        if (!$this->softDeleted) {
            /* Si el modelo no tiene el softdeleted, borramos el recurso completo de la DB */
            $result = $conn->delete($this->table, [$this->identity => $id]);
        } else {
            /* Si el modelo tiene el softdeleted, modificamos la columna que afecta al softdeleted */
            $deleted_at = date("Y-m-d H:i:s", time());
            $data = $this->get([$this->identity => $id]);
            if (!isset($data[$this->softDeleted]) && $data[$this->softDeleted] == null) {
                $result = $this->update([$this->softDeleted => $deleted_at], $id);
            } else {
                $result = new ErrorException('El recurso ya se encuentra eliminado');
            }
        }

        if ($result instanceof ErrorException) {
            logFileEE($this->logPath, $result, get_class($this), __FUNCTION__);
        }
        return $result;
    }

    /**
     * Genera relación de uno a uno     
     *  
     * @access public
     * @param string $class instancia de la clase en se requiere buscar.
     * @param string $source clave foranea del modelo $this.
     * @param string $destiny clave primaria de $class.
     * @return this
     */
    public function hasOne($class, $source, $destiny)
    {
        /* Generamos la instancia de la clase por un string */
        $instance = new $class();

        /* Obtener el nombre del metodo por el cual se llamo hasOne */
        $trace = debug_backtrace();
        $name = $trace[1]['function'];
        $method = $trace[2]['function'];

        if (!in_array($name, $_SESSION['exect'])) {
            $_SESSION['exect'][] = $name;

            /* Estructuramos la información, cuando value no contiene arreglos */
            if ($method == 'get') {
                $data = $instance->get([$destiny => $this->value[$source]]);
                $this->value[$name] = $data->value;
            }

            /* Estructuramos la información, cuando value contiene arreglos */
            if ($method == 'list') {
                foreach ($this->value as $key => $value) {
                    $data = $instance->get([$destiny => $value[$source]]);
                    foreach ($this->reExectMethods as $method) {
                        unset($_SESSION['exect'][array_search($method, $_SESSION['exect'])]);
                    }
                    $this->value[$key][$name] = $data->value;
                }
            }
        }

        return $this;
    }

    public function executeSqlQuery(string $sql, $fetch_assoc = true)
    {
        try {
            $conn = new BaseDatos();
            $query =  $conn->query($sql);

            if ($fetch_assoc) {
                $result = $conn->fetch_assoc($query);
            } else {
                $result = [];
                while ($row = odbc_fetch_array($query)) {
                    $result[] = $row;
                }
            }
            return $result;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    private function filterMethods($methods)
    {
        /* Todos los metodos de BaseModel */
        $filter = $this->filterMethod;

        /* Solamente los metodos de la clase hija */
        return array_values(array_filter(
            $methods,
            function ($method) use ($filter) {
                return !in_array($method, $filter);
            }
        ));
    }

    public function addFilterMethod(array $methods)
    {
        foreach ($methods as $method) {
            $this->filterMethod[] = $method;
        }
    }
}
