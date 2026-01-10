<?php

declare(strict_types=1);

namespace App\Swagger;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Yaml\Parser;

class SwaggerDecorator implements NormalizerInterface
{
    public function __construct(
        private readonly NormalizerInterface $defaultDecorator,
        private readonly string $configLocation,
        private readonly Parser $parser,
        private readonly ParameterBagInterface $params
    ) {
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $baseDoc = $this->defaultDecorator->normalize($object, $format, $context);
        $config = $this->parser->parseFile($this->configLocation);

        if ($config) {
            $docs = $this->mergeDocRecursively($baseDoc, $config);

            if (isset($docs['paths'])) {
                if ($docs['paths'] instanceof \ArrayObject) {
                    $docs['paths']->ksort();
                }

                if (is_array($docs['paths'])) {
                    ksort($docs['paths']);
                }
            }

            return $docs;
        }

        return $baseDoc;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->defaultDecorator->supportsNormalization($data, $format);
    }

    private function mergeDocRecursively($baseDoc, array $config)
    {
        foreach ($config as $key => $value) {
            // Check for wildcard removal
            if ($value == null && substr($key, strlen($key) - 1) == '*') {
                $matchKeyBase = substr($key, 0, strlen($key) - 1);
                $matchKeySet = array_keys((array) $baseDoc);

                $matchKeySet = array_filter($matchKeySet, fn ($matchKey) => str_starts_with($matchKey, $matchKeyBase));

                foreach ($matchKeySet as $matchKey) {
                    unset($baseDoc[$matchKey]);
                }

                continue;
            }

            $baseDoc[$key] = is_array($value)
                ? $this->buildOverwriteConfig($baseDoc, $key, $value)
                : $this->replaceParameterPlaceholders($value);

            if (is_null($baseDoc[$key])) {
                unset($baseDoc[$key]);
            }
        }

        return $baseDoc;
    }

    private function buildOverwriteConfig($baseDoc, $key, array $value)
    {
        if (isset($baseDoc[$key]) && $this->hasChildren($baseDoc[$key])) {
            return $this->mergeDocRecursively($baseDoc[$key], $value);
        }

        foreach ($value as $key => $leaf) {
            $value[$key] = $this->replaceParameterPlaceholders($leaf);
        }

        return $value;
    }

    private function hasChildren($docObject): bool
    {
        return is_array($docObject) || is_object($docObject);
    }

    private function replaceParameterPlaceholders($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $leaf) {
                $value[$key] = $this->replaceParameterPlaceholders($leaf);
            }

            return $value;
        }

        if (is_string($value) && str_contains($value, '%')) {
            preg_match_all('/%([\w()-\.]+)%/', $value, $tokens);

            foreach ($tokens[1] ?? [] as $token) {
                $value = str_replace('%' . $token . '%', $this->params->get($token), $value);
            }
        }

        return $value;
    }
}
