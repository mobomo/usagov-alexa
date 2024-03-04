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

/* modules/custom/alexa2/alexa2_demo/templates/alexa2-demo-wizard.html.twig */
class __TwigTemplate_b06cba1d1059f11dd572c21ff89137e9 extends Template
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
        echo "<h1>Wizard Page</h1>

<div id=\"react-root\"></div>

";
        // line 6
        if (twig_test_empty(($context["wizard_tree"] ?? null))) {
            // line 7
            echo "    <h3>Invalid wizard path.</h3>
";
        } else {
            // line 9
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["wizard_tree"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["wizard_step"]) {
                // line 10
                echo "        <p>";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["wizard_step"], "title", [], "any", false, false, true, 10), 10, $this->source), "html", null, true);
                echo " - ";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["wizard_step"], "body", [], "any", false, false, true, 10), 10, $this->source), "html", null, true);
                echo "</p>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['wizard_step'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
        }
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["wizard_tree"]);    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "modules/custom/alexa2/alexa2_demo/templates/alexa2-demo-wizard.html.twig";
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
        return array (  56 => 10,  51 => 9,  47 => 7,  45 => 6,  39 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<h1>Wizard Page</h1>

<div id=\"react-root\"></div>

{# Liam - Comment out or remove below when adding React. #}
{% if wizard_tree is empty %}
    <h3>Invalid wizard path.</h3>
{% else %}
    {% for wizard_step in wizard_tree %}
        <p>{{ wizard_step.title }} - {{ wizard_step.body }}</p>
    {% endfor %}
{% endif %}", "modules/custom/alexa2/alexa2_demo/templates/alexa2-demo-wizard.html.twig", "/mnt/c/MAMP/htdocs/usagov-alexa-simple-mvp/web/modules/custom/alexa2/alexa2_demo/templates/alexa2-demo-wizard.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("if" => 6, "for" => 9);
        static $filters = array("escape" => 10);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for'],
                ['escape'],
                []
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
