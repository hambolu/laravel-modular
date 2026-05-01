<?php

namespace LaravelModular\Traits;

trait HasDtoMapping
{
    /**
     * Fill a DTO from an array, request, or model.
     */
    public static function from(array|object $data): static
    {
        $dto = new static();
        $props = get_object_vars($dto);
        $input = is_array($data) ? $data : (method_exists($data, 'toArray') ? $data->toArray() : (array) $data);

        foreach (array_keys($props) as $prop) {
            $key = $prop;
            if (array_key_exists($key, $input)) {
                $dto->{$prop} = $input[$key];
            }
        }

        return $dto;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->toArray(), array_flip($keys));
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->toArray(), array_flip($keys));
    }
}
