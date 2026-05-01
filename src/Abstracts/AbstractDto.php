<?php

namespace LaravelModular\Abstracts;

use LaravelModular\Traits\HasDtoMapping;
use Illuminate\Support\Collection;

/**
 * Base Data Transfer Object
 *
 * Usage:
 *   class CreateUserDto extends AbstractDto {
 *       public string $name;
 *       public string $email;
 *       public string $password;
 *   }
 *
 *   $dto = CreateUserDto::from($request->validated());
 *   $dto = CreateUserDto::from(['name' => 'John', 'email' => 'j@j.com']);
 */
abstract class AbstractDto
{
    use HasDtoMapping;

    public static function fromRequest(\Illuminate\Http\Request $request): static
    {
        return static::from($request->all());
    }

    public static function fromModel(\Illuminate\Database\Eloquent\Model $model): static
    {
        return static::from($model->toArray());
    }

    public static function collection(array $items): Collection
    {
        return collect($items)->map(fn($item) => static::from($item));
    }

    public function with(array $overrides): static
    {
        $data = array_merge($this->toArray(), $overrides);
        return static::from($data);
    }
}
