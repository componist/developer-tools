<x:developerTools::layouts.package>
    <div>
        <div class="container px-3 mx-auto ">
            <div class="grid grid-cols-1 gap-7">

                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">Create Methode</p>
                    <pre data-enlighter-language="php">
public function create(CreateUserRequest $request): RedirectResponse
{
    $user->create($request->safe());
    return redirect()->route('users.index');
}

</pre>
                </div>

                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">Show Methode</p>
                    <pre data-enlighter-language="php">
public function show(User $user): View
{
    return view('user.show',[
      'content' => $user
    ]);
}

</pre>
                </div>



                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">Edit Methode</p>
                    <pre data-enlighter-language="php">
public function edit(User $user): View
{
    return view('user.edit',[
      'content' => $user
    ]);
}

</pre>
                </div>



                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">Update Methode</p>
                    <pre data-enlighter-language="php">
public function update(UpdateUserRequest $request, User $user): RedirectResponse
{
    $user->update($request->safe());
    return redirect()->route('users.edit', $user);
}

</pre>
                </div>

                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">Delete Methode</p>
                    <pre data-enlighter-language="php">
public function destroy(User $user): RedirectResponse
{
    if($user->delete()){
      return redirect()->route('users.index');
    }
}
</pre>
                </div>


                <div class="p-5 bg-[#282a36] dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-md text-teal-500">
                    <p class="mb-3 font-bold">Test Case</p>
                    <pre data-enlighter-language="php">               
namespace Tests\Feature\Controllers\BlogController;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;
class UpdateTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function user_can_be_updated(): void
    {
        $user = User::factory()->create();

        $patchData = [
          'name' => 'A new name',
          'email' => 'new-email@example.com',
        ];

        $this->patch(route('users.update', $user),$patchData)->assertRedirect();
    }
}

</pre>
                </div>
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
</x:developerTools::layouts.package>
