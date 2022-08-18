<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class DependentBelongsToFieldTest extends DuskTestCase
{
    public function test_it_can_apply_depends_on_first_load()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertDontSeeIn('@nova-form', 'Attachment');

            $browser->loginAs(1)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertSeeIn('@nova-form', 'Attachment');

            $browser->loginAs(4)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertDontSeeIn('@nova-form', 'Attachment')
                ->type('@title', 'Space Pilgrim: Episode 1')
                ->pause(2000)
                ->assertSelected('@user', 1)
                ->assertInputValue('@key-value-key-0', 'Series')
                ->assertInputValue('@key-value-value-0', 'Space Pilgrim')
                ->assertSeeIn('@nova-form', 'Attachment')
                ->cancel();

            $browser->blank();
        });
    }

    public function test_it_can_submit_depends_on_belongs_to_on_first_load()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertDontSeeIn('@nova-form', 'Attachment')
                ->type('@title', 'Space Pilgrim: Episode 1')
                ->type('@body', 'Content')
                ->pause(2000)
                ->assertSelected('@user', 1)
                ->assertInputValue('@key-value-key-0', 'Series')
                ->assertInputValue('@key-value-value-0', 'Space Pilgrim')
                ->create();

            $browser->blank();

            $this->assertDatabaseHas('posts', [
                'user_id' => 1,
                'title' => 'Space Pilgrim: Episode 1',
                'body' => 'Content',
            ]);

            tap(Post::latest()->first(), function ($post) {
                $this->assertSame(['Series' => 'Space Pilgrim'], $post->meta);
            });
        });
    }

    public function test_it_can_retrieve_correct_dependent_state_on_edit()
    {
        $this->browse(function (Browser $browser) {
            $post1 = PostFactory::new()->create(['user_id' => 4]);
            $post2 = PostFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Update('posts', $post1->id))
                ->assertDontSeeIn('@nova-form', 'Attachment')
                ->type('@title', 'Space Pilgrim: Episode '.$post1->id)
                ->pause(2000)
                ->assertSelected('@user', 4)
                ->cancel();

            $browser->visit(new Update('posts', $post2->id))
                ->assertSeeIn('@nova-form', 'Attachment')
                ->type('@title', 'Space Pilgrim: Episode '.$post2->id)
                ->pause(2000)
                ->assertSelected('@user', $post2->user_id)
                ->cancel();

            $browser->blank();
        });
    }
}
