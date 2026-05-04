<?php

namespace LaravelModular\Abstracts;

use LaravelModular\Traits\HasDtoMapping;
use Illuminate\Support\Collection;

/**
 * Base Data Transfer Object.
 *
 * Usage:
 *   class CreateUserDto extends AbstractDto {
 *       public string $name  = '';
 *       public string $email = '';
 *   }
 *
 *   $dto = CreateUserDto::from($request->validated());
 *   $dto = CreateUserDto::fromModel($user);
 */
abstract class AbstractDto
{
    use HasDtoMapping;

    public static function fromRequest(\Illuminate\Http\Request $request): static
    {
        return static::from($request->validated());
    }

    public static function fromModel(\Illuminate\Database\Eloquent\Model $model): static
    {
        return static::from($model->toArray());
    }

    /**
     * Collect items and cast each to this DTO.
     */
    public static function collection(array $items): Collection
    {
        return collect($items)->map(fn($item) => static::from($item));
    }

    /**
     * Return a new instance with overridden values.
     */
    public function with(array $overrides): static
    {
        $data = array_merge($this->toArray(), $overrides);
        return static::from($data);
    }

    /**
     * Validate the DTO properties using Laravel Validator.
     *
     * Override rules() in your DTO to enable validation.
     */
    public function validate(): static
    {
        if (method_exists($this, 'rules')) {
            $validator = \Illuminate\Support\Facades\Validator::make($this->toArray(), $this->rules());
            $validator->validate();
        }
        return $this;
    }

    /**
     * Cast all DTO values to JSON-serializable form.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
