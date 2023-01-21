<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        $todos = Todo::where('user_id', $user->id)->get();
        //$ativas = Todo::where('is_complete', '==', '1')->get();
        return view('dashboard', compact('user', 'todos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        logger()->debug('Request', [$request->color]);
        try {
            $user = auth()->user();

            $attributes = $request->only([
                'title',
                'description',
                'color'
            ]);

            $attributes['user_id'] = $user->id;

            $todo = Todo::create($attributes);
        } catch (\Throwable $th) {
            logger()->error($th);
            return redirect('/todos/create')->with('error', 'Erro ao criar TODO');
        }

        return redirect('/dashboard')->with('success', 'TODO criado com sucesso');
    }

    /**
     * Complete the specified resource in storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function complete(Todo $todo)
    {
        try {
            $user = auth()->user();

            // Verificar se TODO é do usuário
            if ($todo->user_id !== $user->id) {
                return response('', 403);
            }

            $todo->update(['is_complete' => true]);
        } catch (\Throwable $th) {
            logger()->error($th);
            return redirect('/dashboard')->with('error', 'Erro ao completar TODO');
        }

        return redirect('/dashboard')->with('success', 'TODO completado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        try {
            $user = auth()->user();
            // Verificar se TODO é do usuário
            if ($todo->user_id !== $user->id) {
                return response('', 403);
            }

            $todo->delete();
        } catch (\Throwable $th) {
            logger()->error($th);
            return redirect('/dashboard')->with('error', 'Erro ao deletar TODO');
        }

        return redirect('/dashboard')->with('success', 'TODO deletado com sucesso');
    }

    public function edit($id)
    {
        $todo = Todo::find($id);
        $user = auth()->user();
        // Verificar se TODO é do usuário
        if ($todo->user_id !== $user->id) {
            return response('', 404);
        }

        return view('edit', compact('todo'));
        
    }

    public function update($id, Request $request)
    {
        $todo = Todo::find($id);
        $user = auth()->user();
        
        if ($todo->user_id !== $user->id) {
            return response('', 403);
        }

        $todo->update([
            'title' => $request->title,
            'color' => $request->color
        ]);

        return redirect('/dashboard');

    }
}
