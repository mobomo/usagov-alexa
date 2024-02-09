<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* modules/custom/alexa2/alexa2_demo/templates/alexa2-demo-wizard-select.html.twig */
class __TwigTemplate_f9f04cc683a8bcf08588cfc56e5fbe2e extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<h1>Wizard Select Page</h1>
<div id=\"react-root\"></div>

";
        // line 4
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("alexa2_demo/alexa2_demo.react_wizard_steps"), "html", null, true);
        echo "


";
        // line 8
        if (twig_test_empty(($context["wizards"] ?? null))) {
            // line 9
            echo "    <h3>Sorry, there are no available wizards.</h3><a href=\"";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("node.add", ["node_type" => "wizard"]));
            echo "\"><button class=\"btn btn-primary\">Create a New Wizard</button></a>
";
        } else {
            // line 11
            echo "    <style>
        #wizard-selection.disabled {
            pointer-events: none;
            filter: grayscale(1.0);
            opacity: 0.5;
            cursor: default;
        }
    </style>
    <div id=\"wizard-selection\">
        <h3>Select the wizard you want to manage.</h3>
        <ul style=\"list-style-type: none;\">
            ";
            // line 22
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["wizards"] ?? null));
            $context['loop'] = [
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            ];
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["_key"] => $context["wizard"]) {
                // line 23
                echo "                <li>
                    <label class=\"container\" style=\"cursor: pointer;\">
                        <input type=\"radio\" name=\"wizard\" id=\"option_";
                // line 25
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["wizard"], "id", [], "any", false, false, true, 25), 25, $this->source), "html", null, true);
                echo "\"
                            wizard-path=\"";
                // line 26
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getPath("alexa2_demo.wizards.wizard", ["wizard" => twig_get_attribute($this->env, $this->source, $context["wizard"], "id", [], "any", false, false, true, 26)]), "html", null, true);
                echo "\"
                            name=\"radio\" style=\"cursor: pointer;\" ";
                // line 27
                if (twig_get_attribute($this->env, $this->source, $context["loop"], "first", [], "any", false, false, true, 27)) {
                    echo "checked";
                }
                echo ">
                        &nbsp;";
                // line 28
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $context["wizard"], "title", [], "any", false, false, true, 28), "value", [], "any", false, false, true, 28), 28, $this->source), "html", null, true);
                echo "
                    </label>
                </li>
            ";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['length'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['wizard'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 32
            echo "        </ul>
        <button class=\"btn btn-primary\" onclick=\"selectWizard()\" style=\"margin-right: 10px;\">
            <b>Submit</b>
        </button>
        <a href=\"";
            // line 36
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("node.add", ["node_type" => "wizard"]));
            echo "\"><button class=\"btn btn-primary\"><b>Create a New Wizard</b></button></a>
    </div>
    <script>
        
        function selectWizard() {
            // TODO disable radios and buttons, show spinner
            // bad css disable below could be replaced with proper method
            document.getElementById('wizard-selection').classList.add('disabled');
            let selectedWizardPath = document.querySelector('input[name=\"wizard\"]:checked').getAttribute('wizard-path');
            window.location.href = selectedWizardPath;
            // TODO error fallback with message in case this fails for whatever reason
        }

    </script>
";
        }
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["wizards", "loop"]);    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "modules/custom/alexa2/alexa2_demo/templates/alexa2-demo-wizard-select.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  130 => 36,  124 => 32,  106 => 28,  100 => 27,  96 => 26,  92 => 25,  88 => 23,  71 => 22,  58 => 11,  52 => 9,  50 => 8,  44 => 4,  39 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<h1>Wizard Select Page</h1>
<div id=\"react-root\"></div>

{{ attach_library('alexa2_demo/alexa2_demo.react_wizard_steps') }}


{# Liam - Comment out or remove below when adding React. #}
{% if wizards is empty %}
    <h3>Sorry, there are no available wizards.</h3><a href=\"{{ path('node.add', {'node_type': 'wizard'}) }}\"><button class=\"btn btn-primary\">Create a New Wizard</button></a>
{% else %}
    <style>
        #wizard-selection.disabled {
            pointer-events: none;
            filter: grayscale(1.0);
            opacity: 0.5;
            cursor: default;
        }
    </style>
    <div id=\"wizard-selection\">
        <h3>Select the wizard you want to manage.</h3>
        <ul style=\"list-style-type: none;\">
            {% for wizard in wizards %}
                <li>
                    <label class=\"container\" style=\"cursor: pointer;\">
                        <input type=\"radio\" name=\"wizard\" id=\"option_{{ wizard.id }}\"
                            wizard-path=\"{{ path('alexa2_demo.wizards.wizard', { 'wizard': wizard.id }) }}\"
                            name=\"radio\" style=\"cursor: pointer;\" {% if loop.first %}checked{% endif %}>
                        &nbsp;{{ wizard.title.value}}
                    </label>
                </li>
            {% endfor %}
        </ul>
        <button class=\"btn btn-primary\" onclick=\"selectWizard()\" style=\"margin-right: 10px;\">
            <b>Submit</b>
        </button>
        <a href=\"{{ path('node.add', {'node_type': 'wizard'}) }}\"><button class=\"btn btn-primary\"><b>Create a New Wizard</b></button></a>
    </div>
    <script>
        
        function selectWizard() {
            // TODO disable radios and buttons, show spinner
            // bad css disable below could be replaced with proper method
            document.getElementById('wizard-selection').classList.add('disabled');
            let selectedWizardPath = document.querySelector('input[name=\"wizard\"]:checked').getAttribute('wizard-path');
            window.location.href = selectedWizardPath;
            // TODO error fallback with message in case this fails for whatever reason
        }

    </script>
{% endif %}", "modules/custom/alexa2/alexa2_demo/templates/alexa2-demo-wizard-select.html.twig", "/mnt/c/MAMP/htdocs/mobomo-usagov-alexa/web/modules/custom/alexa2/alexa2_demo/templates/alexa2-demo-wizard-select.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("if" => 8, "for" => 22);
        static $filters = array("escape" => 4);
        static $functions = array("attach_library" => 4, "path" => 9);

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for'],
                ['escape'],
                ['attach_library', 'path']
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
