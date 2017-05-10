<?php
namespace ContinuousNet\LivnYouBundle\Repository;

interface IRepository
{
    public function getAll($params = []);

    public function countAll();

    public function get($params = []);

    public function store($entity, $params= []);

    public function update($entity, $params = []);

    public function delete($idEntity);
}
