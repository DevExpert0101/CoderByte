<?php
class Travel
{
    public $companyId;
    public $price;
    public function __construct($travelData)
    {
        $this->companyId = $travelData['companyId'];
        $this->price = (float)$travelData['price'];
    }
}
class Company
{
    public $id;
    public $createdAt;
    public $name;
    public $parentId;
    public $cost;
    public $children;
    public function __construct($companyData)
    {
        $this->id = $companyData['id'];
        $this->createdAt = $companyData['createdAt'];
        $this->name = $companyData['name'];
        $this->parentId = $companyData['parentId'];
        $this->cost = 0;
        $this->children = [];
    }
    public function addChild($child)
    {
        $this->children[] = $child;
    }
    public function addCost($cost)
    {
        $this->cost += $cost;
    }
}
class TestScript
{
    private $companies = [];
    private $travels = [];
    private function fetchCompanies()
    {
        $companyData = file_get_contents('5f27781bf5d27e001612e057.mockapi.io/webprovise/companies');
        $companies = json_decode($companyData, true);
        foreach ($companies as $company) {
            $this->companies[$company['id']] = new Company($company);
        }
    }
    private function fetchTravels()
    {
        $travelData = file_get_contents('5f27781bf5d27e001612e057.mockapi.io/webprovise/travels');
        $travels = json_decode($travelData, true);
        foreach ($travels as $travel) {
            $this->travels[] = new Travel($travel);
        }
    }
    private function buildCompanyTree()
    {
        foreach ($this->companies as $company) {
            if ($company->parentId !== '0' && isset($this->companies[$company->parentId])) {
                $this->companies[$company->parentId]->addChild($company);
            }
        }
    }
    private function calculateInitialCosts()
    {
        foreach ($this->travels as $travel) {
            if (isset($this->companies[$travel->companyId])) {
                $this->companies[$travel->companyId]->addCost($travel->price);
            }
        }
    }
    private function recalculateCosts($company)
    {
        foreach ($company->children as $child) {
            $company->addCost($this->recalculateCosts($child));
        }
        return $company->cost;
    }
    public function execute()
    {
        $start = microtime(true);
        $this->fetchCompanies();
        $this->fetchTravels();
        $this->calculateInitialCosts();
        $this->buildCompanyTree();
        $result = [];
        foreach ($this->companies as $company) {
            if ($company->parentId === '0') {
                $this->recalculateCosts($company);
                $result[] = $company;
            }
        }
        echo json_encode($result, JSON_PRETTY_PRINT);
        echo "nTotal time: " . (microtime(true) - $start);
    }
}
(new TestScript())->execute();
?>