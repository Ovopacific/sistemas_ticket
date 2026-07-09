<?php
/**
 * Help Desk LAN - Department and Category CRUD Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\DepartmentModel;
use App\Models\CategoryModel;
use App\Helpers\Auditor;

class DepartmentController extends Controller {
    
    /**
     * Show departments list and create form modal.
     */
    public function index(Request $request): void {
        $this->authorize(['admin']);
        $departments = DepartmentModel::getAll();
        $this->render('departments/index', ['departments' => $departments], 'Gestión de Departamentos');
    }

    /**
     * Store a new Department.
     */
    public function store(Request $request): void {
        $this->authorize(['admin']);
        
        $name = trim($request->post('name', ''));
        $description = trim($request->post('description', ''));

        if (empty($name)) {
            $this->session->setFlash('error', 'El nombre del departamento es requerido.');
        } else {
            $id = DepartmentModel::create($name, $description);
            $currentUser = $this->session->get('user');
            Auditor::log($currentUser['id'], 'DEPT_CREATE', "Creación de departamento: {$name} (ID: {$id})");
            $this->session->setFlash('success', 'Departamento creado con éxito.');
        }

        $this->response->redirect('/departments');
    }

    /**
     * Update Department details.
     */
    public function update(Request $request, string $id): void {
        $this->authorize(['admin']);
        $deptId = (int)$id;
        $name = trim($request->post('name', ''));
        $description = trim($request->post('description', ''));

        if (empty($name)) {
            $this->session->setFlash('error', 'El nombre no puede estar vacío.');
        } else {
            DepartmentModel::update($deptId, $name, $description);
            $currentUser = $this->session->get('user');
            Auditor::log($currentUser['id'], 'DEPT_UPDATE', "Modificación de departamento ID: {$deptId}");
            $this->session->setFlash('success', 'Departamento actualizado.');
        }

        $this->response->redirect('/departments');
    }

    /**
     * Delete Department.
     */
    public function delete(Request $request, string $id): void {
        $this->authorize(['admin']);
        $deptId = (int)$id;

        DepartmentModel::delete($deptId);
        $currentUser = $this->session->get('user');
        Auditor::log($currentUser['id'], 'DEPT_DELETE', "Eliminación de departamento ID: {$deptId}");
        
        $this->session->setFlash('success', 'Departamento eliminado.');
        $this->response->redirect('/departments');
    }

    /**
     * Show categories list.
     */
    public function categories(Request $request): void {
        $this->authorize(['admin']);
        $categories = CategoryModel::getAll();
        $this->render('categories/index', ['categories' => $categories], 'Gestión de Categorías');
    }

    /**
     * Store category.
     */
    public function storeCategory(Request $request): void {
        $this->authorize(['admin']);
        $name = trim($request->post('name', ''));
        $description = trim($request->post('description', ''));

        if (empty($name)) {
            $this->session->setFlash('error', 'El nombre de categoría es obligatorio.');
        } else {
            $id = CategoryModel::create($name, $description);
            $currentUser = $this->session->get('user');
            Auditor::log($currentUser['id'], 'CAT_CREATE', "Creación de categoría: {$name} (ID: {$id})");
            $this->session->setFlash('success', 'Categoría creada.');
        }
        $this->response->redirect('/categories');
    }

    /**
     * Update category.
     */
    public function updateCategory(Request $request, string $id): void {
        $this->authorize(['admin']);
        $catId = (int)$id;
        $name = trim($request->post('name', ''));
        $description = trim($request->post('description', ''));

        if (empty($name)) {
            $this->session->setFlash('error', 'El nombre no puede estar vacío.');
        } else {
            CategoryModel::update($catId, $name, $description);
            $currentUser = $this->session->get('user');
            Auditor::log($currentUser['id'], 'CAT_UPDATE', "Modificación de categoría ID: {$catId}");
            $this->session->setFlash('success', 'Categoría actualizada.');
        }
        $this->response->redirect('/categories');
    }

    /**
     * Delete category.
     */
    public function deleteCategory(Request $request, string $id): void {
        $this->authorize(['admin']);
        $catId = (int)$id;

        CategoryModel::delete($catId);
        $currentUser = $this->session->get('user');
        Auditor::log($currentUser['id'], 'CAT_DELETE', "Eliminación de categoría ID: {$catId}");
        
        $this->session->setFlash('success', 'Categoría eliminada.');
        $this->response->redirect('/categories');
    }
}
