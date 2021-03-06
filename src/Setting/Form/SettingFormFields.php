<?php namespace Anomaly\SettingsModule\Setting\Form;

use Anomaly\SettingsModule\Setting\Contract\SettingRepositoryInterface;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Bus\SelfHandling;

/**
 * Class SettingFormFields
 *
 * @link          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\SettingsModule\Setting\Form
 */
class SettingFormFields implements SelfHandling
{

    /**
     * The config repository.
     *
     * @var Repository
     */
    protected $config;

    /**
     * Create a new SettingFormFields instance.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Return the form fields.
     *
     * @param SettingFormBuilder $builder
     */
    public function handle(SettingFormBuilder $builder, SettingRepositoryInterface $settings)
    {
        $namespace = $builder->getFormEntry() . '::';

        /**
         * Get the fields from the config system. Sections are
         * optionally defined the same way.
         */
        if (!$fields = $this->config->get($namespace . 'settings/settings')) {
            $fields = $fields = $this->config->get($namespace . 'settings', []);
        }

        if ($sections = $this->config->get($namespace . 'settings/sections')) {
            $builder->setSections($sections);
        }

        /**
         * Finish each field.
         */
        foreach ($fields as $slug => &$field) {

            /**
             * Force an array. This is done later
             * too in normalization but we need it now
             * because we are normalizing / guessing our
             * own parameters somewhat.
             */
            if (is_string($field)) {
                $field = [
                    'type' => $field
                ];
            }

            // Make sure we have a config property.
            $field['config'] = array_get($field, 'config', []);


            if (trans()->has(
                $label = array_get(
                    $field,
                    'label',
                    $namespace . 'setting.' . $slug . '.label'
                )
            )
            ) {
                $field['label'] = trans($label);
            }

            // Default the label.
            $field['label'] = trans(
                array_get(
                    $field,
                    'label',
                    $namespace . 'setting.' . $slug . '.name'
                )
            );

            // Default the placeholder.
            if (trans()->has(
                $placeholder = array_get(
                    $field,
                    'placeholder',
                    $namespace . 'setting.' . $slug . '.placeholder'
                )
            )
            ) {
                $field['placeholder'] = trans($placeholder);
            }

            // Default the instructions.
            if (trans()->has(
                $instructions = array_get(
                    $field,
                    'instructions',
                    $namespace . 'setting.' . $slug . '.instructions'
                )
            )
            ) {
                $field['instructions'] = trans($instructions);
            }

            // Get the value defaulting to the default value.
            $field['value'] = $settings->get($namespace . $slug, array_get($field['config'], 'default_value'));
        }

        $builder->setFields($fields);
    }
}
