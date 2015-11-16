<?php
namespace Djaney\DoctrineFacade;
class EntityFacade {

    protected $doctrine;
    protected $em = null;
    protected $className = null;

    private function clean(){
        $this->subject = null;
        return $this;
    }

    private function finish(){
        $this->em->flush();
        return $this->clean();
    }

    private function setterMethod($field){
        return 'set' . ucfirst($field);
    }

    private function getterMethod($field){
        return 'get' . ucfirst($field);
    }

    public function __construct($doctrine,$className = null){
        $this->doctrine = $doctrine;
        $this->className = $className;
        $this->em = $this->doctrine->getManager();
    }

    public function getClass(){
        return $this->className;
    }

    public function query($criteria = array(), $limit = 10, $offset = 0, $orderBy = array(), $direction = 'ASC'){
        $col = $this->doctrine
            ->getRepository($this->className)
            ->findBy($criteria, $orderBy, $limit, $offset);
        return $col;
    }

    public function get($id){
        return $this
            ->doctrine
            ->getRepository($this->className)
            ->find($id);
    }

    private function patcher($subj,$attrs){
        if($subj===null) throw new InvalidFacadeSubjectException();
        $r = new \ReflectionClass($subj);
        foreach($attrs as $k=>$v){
            if( $k=='id' ) continue;
            $method = $this->setterMethod($k);
            if( $r->hasMethod( $method ) ){
                $subj->$method($v);
            }
        }
    }

    public function post($attrs){

        $r = new \ReflectionClass($this->className);
        $subj = $r->newInstanceArgs();
        $this->em->persist($subj);
        $this->patcher($subj,$attrs);
        $this->em->flush();
        return $subj;
    }

    public function mutate($id, \Closure $callback){
        $subj = $this->get($id);
        if($subj===null) throw new InvalidFacadeSubjectException();
        $callback($subj);
        $this->em->flush();
        return $this;
    }

    public function patch($id, $attrs){
        $subj = $this->get($id);
        $this->patcher($subj,$attrs);
        $this->em->flush();
        return $subj;
    }

    public function delete($id){
        $subj = $this->get($id);
        if($subj===null) throw new InvalidFacadeSubjectException();
        $this->em->remove($subj);
        $this->em->flush();
    }

}
