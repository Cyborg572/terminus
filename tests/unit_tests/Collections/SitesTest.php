<?php

namespace Pantheon\Terminus\UnitTests\Collections;

use Pantheon\Terminus\Collections\SiteOrganizationMemberships;
use Pantheon\Terminus\Collections\Sites;
use Pantheon\Terminus\Collections\Workflows;
use Pantheon\Terminus\Models\Environment;
use Pantheon\Terminus\Models\Organization;
use Pantheon\Terminus\Models\Site;
use Pantheon\Terminus\Models\SiteOrganizationMembership;
use Pantheon\Terminus\Models\User;
use Pantheon\Terminus\Models\Workflow;
use Pantheon\Terminus\Session\Session;

/**
 * Class SitesTest
 * Testing class for Pantheon\Terminus\Collections\Sites
 * @package Pantheon\Terminus\UnitTests\Collections
 */
class SitesTest extends CollectionTestCase
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var User
     */
    protected $user;
    /**
     * @var Workflow
     */
    protected $workflow;
    /**
     * @var Workflows
     */
    protected $workflows;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->workflows = $this->getMockBuilder(Workflows::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->workflow = $this->getMockBuilder(Workflow::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->session->method('getUser')
            ->with()
            ->willReturn($this->user);
        $this->user->method('getWorkflows')
            ->with()
            ->willReturn($this->workflows);
    }

    public function testCreate()
    {
        $params = ['param1', 'param2'];

        $this->workflows->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('create_site'),
                $this->equalTo(compact('params'))
            )
            ->willReturn($this->workflow);

        $this->collection = $this->_createSites();
        $out = $this->collection->create($params);
        $this->assertEquals($out, $this->workflow);
    }

    public function testCreateForMigration()
    {
        $params = ['param1', 'param2'];

        $this->workflows->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('create_site_for_migration'),
                $this->equalTo(compact('params'))
            )
            ->willReturn($this->workflow);

        $this->collection = $this->_createSites();
        $out = $this->collection->createForMigration($params);
        $this->assertEquals($out, $this->workflow);
    }

    public function testFetch()
    {
        $site1 = $this->getMockBuilder(Site::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([(object)['id' => 'site1',], ['collection' => $this->collection,]])
            ->getMock();
        $site1->memberships = ['orgmembership', 'usermembership',];
        $site2 = $this->getMockBuilder(Site::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([(object)['id' => 'site2',], ['collection' => $this->collection,]])
            ->getMock();
        $site2->memberships = ['usermembership',];
        $org_memberships = $this->getMockBuilder(SiteOrganizationMemberships::class)
            ->disableOriginalConstructor()
            ->getMock();
        $org_membership = $this->getMockBuilder(SiteOrganizationMembership::class)
            ->disableOriginalConstructor()
            ->getMock();
        $org = $this->getMockBuilder(Organization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->user->expects($this->once())
            ->method('getSites')
            ->with()
            ->willReturn([$site1, $site2,]);
        $this->user->expects($this->once())
            ->method('getOrgMemberships')
            ->with()
            ->willReturn($org_memberships);
        $org_memberships->expects($this->once())
            ->method('fetch')
            ->with()
            ->willReturn($org_memberships);
        $org_memberships->expects($this->once())
            ->method('all')
            ->with()
            ->willReturn([$org_membership,]);
        $org_membership->expects($this->once())
            ->method('get')
            ->with($this->equalTo('role'))
            ->willReturn('admin');
        $org_membership->expects($this->once())
            ->method('getOrganization')
            ->with()
            ->willReturn($org);
        $org->expects($this->once())
            ->method('getSites')
            ->with()
            ->willReturn([$site1,]);

        $this->collection = $this->_createSites();
        $out = $this->collection->fetch();
        $this->assertEquals($this->collection, $out);
    }

    public function testFilterByName()
    {
        /**
        $this->collection = $this->_createSitesForFilter(['filterByName',]);

        foreach ($this->model_data as $id => $data) {
            $this->collection->expects($this->once())
                ->method('get')
                ->with($this->equalTo('name'))
                ->willReturn($data->name);
        }

        $this->assertEquals(print_r($this->collection->all(), true), 'hiu');
        $out = $this->collection->filterByName('piglet');
        $this->assertEquals(print_r($this->collection->all(), true), 'hiu');
        */
    }

    public function testFilterByOwner()
    {
    }

    public function testFilterByTag()
    {
    }

    /**
     * @return mixed
     */
    protected function _createSites()
    {
        $sites = new Sites();
        $sites->setSession($this->session);
        return $sites;
    }

    /**
     * @param array $functions Functions to enable
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createSitesForFilter(array $functions = [])
    {
        $this->model_data = [
            'a' => (object)['name' => 'piglet', 'id' => 'a', 'owner' => 'person a',],
            'b' => (object)['name' => 'duckling', 'id' => 'b', 'owner' => 'person a',],
            'c' => (object)['name' => 'kitten', 'id' => 'c', 'owner' => 'person b',],
        ];

        $models = [];
        foreach ($this->model_data as $id => $data) {
            $models[$id] = $this->getMockBuilder(Site::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        $sites = $this->getMockBuilder(Sites::class)
            ->setMethods(array_merge(['getMembers', 'all',], $functions))
            ->disableOriginalConstructor()
            ->getMock();
        $sites->expects($this->any())
            ->method('getMembers')
            ->willReturn($models);

        return $sites;
    }
}
