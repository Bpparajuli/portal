<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class BaseCrudController extends Controller
{
    abstract protected function getModelClass(): string;
    abstract protected function getViewPrefix(): string;
    abstract protected function getRoutePrefix(): string;

    public function index(Request $request)
    {
        $query = $this->getModelClass()::query();
        
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(20);
        return view($this->getViewPrefix() . '.index', compact('items'));
    }

    public function show($id)
    {
        $item = $this->getModelClass()::findOrFail($id);
        return view($this->getViewPrefix() . '.show', compact('item'));
    }

    public function destroy($id)
    {
        $item = $this->getModelClass()::findOrFail($id);
        $item->delete();
        return redirect()->route($this->getRoutePrefix() . '.index')
            ->with('success', 'Item deleted successfully.');
    }
}
