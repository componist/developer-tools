<x:developerTools::layouts.package>
    <div>
        <div class="container px-3 mx-auto ">
            <div class="grid grid-cols-1 gap-5">


                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">routes/api.php</p>
                    <pre data-enlighter-language="php">
Use App\Article;

Route::get('articles', function() {
    // If the Content-Type and Accept headers are set to 'application/json', 
    // this will return a JSON structure. This will be cleaned up later.
    return Article::all();
});

Route::get('articles/{id}', function($id) {
    return Article::find($id);
});

Route::post('articles', function(Request $request) {
    return Article::create($request->all);
});

Route::put('articles/{id}', function(Request $request, $id) {
    $article = Article::findOrFail($id);
    $article->update($request->all());
    return $article;
});

Route::delete('articles/{id}', function($id) {
    Article::find($id)->delete();
    return 204;
})

</pre>
                </div>

                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">ArticleController.php</p>
                    <pre data-enlighter-language="php">
use App\Article;

class ArticleController extends Controller
{
    public function index()
    {
        return Article::all();
    }

    public function show($id)
    {
        return Article::find($id);
    }

    public function store(Request $request)
    {
        return Article::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $article->update($request->all());

        return $article;
    }

    public function delete(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return 204;
    }
}

</pre>
                </div>

                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">routes/api.php</p>
                    <pre data-enlighter-language="php">
Route::get('articles', 'ArticleController@index');
Route::get('articles/{id}', 'ArticleController@show');
Route::post('articles', 'ArticleController@store');
Route::put('articles/{id}', 'ArticleController@update');
Route::delete('articles/{id}', 'ArticleController@delete');

</pre>
                </div>

                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">API ArticleController</p>
                    <pre data-enlighter-language="php">
class ArticleController extends Controller
{
    public function index()
    {
        return Article::all();
    }

    public function show(Article $article)
    {
        return $article;
    }

    public function store(Request $request)
    {
        $article = Article::create($request->all());

        return response()->json($article, 201);
    }

    public function update(Request $request, Article $article)
    {
        $article->update($request->all());

        return response()->json($article, 200);
    }

    public function delete(Article $article)
    {
        $article->delete();

        return response()->json(null, 204);
    }
}

</pre>
                </div>



            </div>
            <script>
                EnlighterJS.init('pre', 'code', {
                    language: 'javascript,php, html, css',
                    theme: 'dracula',
                    indent: 2
                });
            </script>
        </div>

    </div>
</x:developerTools::layouts.package>
