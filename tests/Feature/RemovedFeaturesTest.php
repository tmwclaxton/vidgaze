<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RemovedFeaturesTest extends TestCase
{
    public function test_equity_route_name_no_longer_exists(): void
    {
        $this->assertFalse(Route::has('equity'));
    }

    public function test_old_equity_url_permanently_redirects_home(): void
    {
        $this->get('/equity')->assertStatus(301)->assertRedirect('/');
    }

    public function test_chatroom_routes_no_longer_exist(): void
    {
        $this->assertFalse(Route::has('chatroom.show'));
        $this->assertFalse(Route::has('api.chatroom.index'));
    }

    public function test_old_global_chat_urls_permanently_redirect_home(): void
    {
        $this->get('/chatroom/global-chat')->assertStatus(301)->assertRedirect('/');
        $this->get('/chatroom')->assertStatus(301)->assertRedirect('/');
    }

    public function test_shorts_toggle_endpoint_is_gone(): void
    {
        $this->assertFalse(Route::has('api.shorts.toggle'));
        $this->assertFalse(Route::has('shorts.toggle'));
    }
}
