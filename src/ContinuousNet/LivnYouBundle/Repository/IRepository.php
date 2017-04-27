<?php
namespace ContinuousNet\LivnYouBundle\Repository;



interface IRepository {

    public function getAll();

    public function get($id);

    public function store($entity);

    public function update($id, $entity);

    public function delete($id);
}
?>