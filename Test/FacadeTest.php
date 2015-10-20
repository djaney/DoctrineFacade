<?php namespace Djaney\DoctrineFacade\Test;

use Djaney\DoctrineFacade\EntityFacade;

class FacadeTest extends \PHPUnit_Framework_TestCase
{
    private $doctrine;
    private $employee;
    public function setUp()
    {
        // First, mock the object to be used in the test
        $employee = $this
            ->getMockBuilder('AppBundle\Entity\Employee')
            ->setMethods(['getName','getId','setName'])
            ->getMock()
        ;

        $employee->expects($this->any())
            ->method('getName')
            ->willReturn('Geraldine');

        $employee->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        // Now, mock the repository so it returns the mock of the employee
        $employeeRepository = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $employeeRepository->expects($this->any())
            ->method('find')
            ->willReturn($employee);

        // mock the EntityManager to return the mock of the repository
        $entityManager = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($employeeRepository);


        // mock doctrine service
        $doctrine = $this
            ->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->any())
            ->method('getManager')
            ->willReturn($entityManager);

        $doctrine->expects($this->any())
            ->method('getRepository')
            ->willReturn($employeeRepository);

        $this->employee = $employee;
        $this->doctrine = $doctrine;
    }

    public function testGetById(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $e = $ef->getById(1);
        $this->assertEquals('Geraldine', $e->getName());
    }

    public function testSetSubject(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $e = $ef->start($this->employee);
        $this->assertEquals($this->employee, $e->getSubject());
    }

    public function testSetSubjectById(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $e = $ef->start(1);
        $this->assertEquals(1, $e->getSubject()->getId());
    }

    public function testclean(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $ef->start($this->employee);
        $ef->clean();
        $this->assertEquals(null, $ef->getSubject());
    }

    public function testFinish(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');

        // expect manager flush to be called
        $this->doctrine->getManager()->expects($this->once())
            ->method('flush');

        $ef->finish();
    }

    public function testCreate(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $employee = $ef->create(function($o){
            $o->setName('Geraldine');
        })->getSubject();
        $this->assertInstanceOf('AppBundle\Entity\Employee', $employee);
    }

    public function testMutate(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $ef->start(1)
            ->mutate(function($employee){
                $this->assertEquals('Geraldine', $employee->getName());
            });
    }

    /**
     * @expectedException Djaney\DoctrineFacade\InvalidFacadeSubjectException
     */
    public function testMutateInvalidSubjectException(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $ef
            ->mutate(function($employee){
                $this->assertEquals('Geraldine', $employee->getName());
            });
    }

    public function testPatch(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $this->employee->expects($this->once())
            ->method('setName');
        $ef->start(1)
            ->patch(['name'=>'Djane Rey']);
    }
    /**
     * @expectedException Djaney\DoctrineFacade\InvalidFacadeSubjectException
     */
    public function testPatchInvalidSubjectException(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $ef->patch(['name'=>'Djane Rey']);
    }

    public function testDelete(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');

        // expect manager flush to be called
        $this->doctrine->getManager()->expects($this->once())
            ->method('remove');

            $ef->start(1)->delete();
    }
    /**
     * @expectedException Djaney\DoctrineFacade\InvalidFacadeSubjectException
     */
    public function testDeleteInvalidSubjectException(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');

        $ef->delete();
    }

    // public function testCollection(){
    //     $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
    //     $col = $ef->getCollection();
    //     // $this->assertInstanceOf('Djaney\DoctrineFacade\Collection', $col);
    //     $this->assertCount(10, $col);
    // }
}
