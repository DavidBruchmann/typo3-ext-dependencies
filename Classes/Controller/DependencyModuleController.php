<?php

namespace Wdb\Dependencies\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Wdb\Dependencies\Service\DependencyAnalyzerService;

class DependencyModuleController extends ActionController
{
    /**
     * @var DependencyAnalyzerService
     */
    protected $dependencyAnalyzerService;

    public function __construct(DependencyAnalyzerService $dependencyAnalyzerService = null)
    {
        $this->dependencyAnalyzerService = $dependencyAnalyzerService ?? new DependencyAnalyzerService();
    }

    public function indexAction()
    {
        $depth = (int)($this->request->getArgument('depth') ?? 2);
        $filterCategory = $this->request->hasArgument('category') ? $this->request->getArgument('category') : '';
        $filterTag = $this->request->hasArgument('tag') ? $this->request->getArgument('tag') : '';
        $manager = $this->request->hasArgument('manager') ? $this->request->getArgument('manager') : 'composer';

        if ($manager === 'npm') {
            $dependencies = $this->dependencyAnalyzerService->getNpmDependencyTree($depth, $filterCategory, $filterTag);
        } else {
            $dependencies = $this->dependencyAnalyzerService->getDependencyTree($depth, $filterCategory, $filterTag);
        }
        $this->view->assignMultiple([
            'dependencies' => $dependencies,
            'depth' => $depth,
            'filterCategory' => $filterCategory,
            'filterTag' => $filterTag,
            'manager' => $manager
        ]);
    }

    /*
    public function indexAction()
    {
        $depth = (int)($this->request->getArgument('depth') ?? 2);
        $filterCategory = $this->request->hasArgument('category') ? $this->request->getArgument('category') : '';
        $filterTag = $this->request->hasArgument('tag') ? $this->request->getArgument('tag') : '';

        $dependencies = $this->dependencyAnalyzerService->getDependencyTree($depth, $filterCategory, $filterTag);
        $this->view->assignMultiple([
            'dependencies' => $dependencies,
            'depth' => $depth,
            'filterCategory' => $filterCategory,
            'filterTag' => $filterTag,
        ]);
    }
    */
}
