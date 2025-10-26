<?php

namespace Gfarishyan\PaligoNet\Models;

class TranslationExport extends Model {
    protected string $id;

    protected string $status;

    protected string $url;

    protected string $message;

    public function getId() {
        return $this->id ?? null;
    }

    public function getStatus() {
        return $this->status ?? null;
    }

    public function getUrl() {
        return $this->url ?? null;
    }

    public function getMessage() {
        return $this->message ?? null;
    }
}