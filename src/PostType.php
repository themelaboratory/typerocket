<?php
namespace TypeRocket;

class PostType extends Registrable
{

    private $title = null;
    private $form = null;
    private $taxonomies = array();
    private $icon = null;

    /**
     * Set the post type menu icon
     *
     * Add the CSS needed to create the icon for the menu
     *
     * @param $name
     *
     * @return $this
     */
    public function setIcon( $name )
    {
        $name       = strtolower( $name );
        $icons      = new Icons();
        $this->icon = $icons[$name];
        $obj = $this;
        add_action( 'admin_head', function() use ($obj) {
            ?>
            <style type="text/css">
                #adminmenu #menu-posts-<?php echo $obj->getId(); ?> .wp-menu-image:before {
                    font: 400 15px/1 'typerocket-icons' !important;
                    content: '<?php echo $obj->getIcon(); ?>';
                    speak: none;
                    -webkit-font-smoothing: antialiased;
                }
            </style>
            <?php
        } );

        return $this;
    }

    /**
     * Get the post type icon
     *
     * @return null
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * Get the placeholder title
     *
     * @return null
     */
    public function getTitlePlaceholder()
    {
        return $this->title;
    }

    /**
     * Set the placeholder title for the title field
     *
     * @param $text
     *
     * @return $this
     */
    public function setTitlePlaceholder( $text )
    {
        $this->title = (string) $text;

        return $this;
    }

    /**
     * Get the form hook value by key
     *
     * @param $key
     *
     * @return mixed
     */
    public function getForm( $key )
    {
        return $this->form[$key];
    }

    /**
     * Set the form title hook
     *
     * From hook to be added just below the title field
     *
     * @param bool|true|callable $value
     *
     * @return $this
     */
    public function setTitleForm( $value = true )
    {

        if (is_callable( $value )) {
            $this->form['title'] = $value;
        } else {
            $this->form['title'] = true;
        }

        return $this;
    }

    /**
     * Set the form top hook
     *
     * From hook to be added just above the title field
     *
     * @param bool|true|callable $value
     *
     * @return $this
     */
    public function setTopForm( $value = true )
    {
        if (is_callable( $value )) {
            $this->form['top'] = $value;
        } else {
            $this->form['top'] = true;
        }

        return $this;
    }

    /**
     * Set the from bottom hook
     *
     * From hook to be added below the meta boxes
     *
     * @param bool|true|callable $value
     *
     * @return $this
     */
    public function setBottomForm( $value = true )
    {
        if (is_callable( $value )) {
            $this->form['bottom'] = $value;
        } else {
            $this->form['bottom'] = true;
        }

        return $this;
    }

    /**
     * Set the form editor hook
     *
     * From hook to be added below the editor
     *
     * @param bool|true|callable $value
     *
     * @return $this
     */
    public function setEditorForm( $value = true )
    {
        if (is_callable( $value )) {
            $this->form['editor'] = $value;
        } else {
            $this->form['editor'] = true;
        }

        return $this;
    }

    /**
     * Set the rewrite slug for the post type
     *
     * @param $slug
     *
     * @return $this
     */
    public function setSlug( $slug )
    {
        $this->args['rewrite'] = array( 'slug' => Sanitize::dash( $slug ) );

        return $this;
    }

    /**
     * Set the post type to only show in WordPress Admin
     *
     * @return $this
     */
    public function setAdminOnly() {
        $this->args['public'] = false;
        $this->args['has_archive'] = false;
        $this->args['show_ui'] = true;

        return $this;
    }

    /**
     * Get the rewrite slug
     *
     * @return mixed
     */
    public function getSlug()
    {
        return $this->args['rewrite']['slug'];
    }

    /**
     * Make Post Type. Do not use before init hook.
     *
     * @param string $singular singular name is required
     * @param string $plural plural name
     * @param array $settings args override and extend
     */
    public function __construct( $singular, $plural = null, $settings = array() )
    {

        if(is_null($plural)) {
            $plural = Inflect::pluralize($singular);
        }

        // make lowercase
        $singular      = strtolower( $singular );
        $plural        = strtolower( $plural );
        $upperSingular = ucwords( $singular );
        $upperPlural   = ucwords( $plural );

        $labels = array(
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New ' . $upperSingular,
            'edit_item'          => 'Edit ' . $upperSingular,
            'menu_name'          => $upperPlural,
            'name'               => $upperPlural,
            'new_item'           => 'New ' . $upperSingular,
            'not_found'          => 'No ' . $plural . ' found',
            'not_found_in_trash' => 'No ' . $plural . ' found in Trash',
            'parent_item_colon'  => '',
            'search_items'       => 'Search ' . $upperPlural,
            'singular_name'      => $upperSingular,
            'view_item'          => 'View ' . $upperSingular,
        );

        // setup object for later use
        $plural   = Sanitize::underscore( $plural );
        $singular = Sanitize::underscore( $singular );
        $this->id       = ! $this->id ? $singular : $this->id;

        if (array_key_exists( 'capabilities', $settings ) && $settings['capabilities'] === true) :
            $settings['capabilities'] = array(
                'publish_posts'       => 'publish_' . $plural,
                'edit_post'           => 'edit_' . $singular,
                'edit_posts'          => 'edit_' . $plural,
                'edit_others_posts'   => 'edit_others_' . $plural,
                'delete_post'         => 'delete_' . $singular,
                'delete_posts'        => 'delete_' . $plural,
                'delete_others_posts' => 'delete_others_' . $plural,
                'read_post'           => 'read_' . $singular,
                'read_private_posts'  => 'read_private_' . $plural,
            );
        endif;

        $defaults = array(
            'labels'      => $labels,
            'description' => $plural,
            'rewrite'     => array( 'slug' => Sanitize::dash( $this->id ) ),
            'public'      => true,
            'supports'    => array( 'title', 'editor' ),
            'has_archive' => true,
            'taxonomies'  => array()
        );

        if (array_key_exists( 'taxonomies', $settings )) {
            $this->taxonomies       = array_merge( $this->taxonomies, $settings['taxonomies'] );
            $settings['taxonomies'] = $this->taxonomies;
        }

        $this->args = array_merge( $defaults, $settings );

        return $this;
    }

    /**
     * Register post type with WordPress
     *
     * @return $this
     */
    public function register()
    {
        $this->dieIfReserved();

        do_action( 'tr_register_post_type_' . $this->id, $this );
        register_post_type( $this->id, $this->args );

        return $this;
    }

    /**
     * Add meta box to post type
     *
     * @param string|MetaBox $s
     *
     * @return $this
     */
    public function addMetaBox( $s )
    {
        if ( $s instanceof MetaBox ) {
            $s = (string) $s->getId();
        }elseif( is_array($s) ) {
            foreach($s as $n) {
                $this->addMetaBox($n);
            }
        }

        if ( ! in_array( $s, $this->args['supports'] )) {
            $this->args['supports'][] = $s;
        }

        return $this;
    }

    /**
     * Add taxonomy to post type
     *
     * @param string|Taxonomy $s
     *
     * @return $this
     */
    public function addTaxonomy( $s )
    {

        if ( $s instanceof Taxonomy) {
            $s = (string) $s->getId();
        } elseif( is_array($s) ) {
            foreach($s as $n) {
                $this->addTaxonomy($n);
            }
        }

        if ( ! in_array( $s, $this->taxonomies )) {
            $this->taxonomies[]       = $s;
            $this->args['taxonomies'] = $this->taxonomies;
        }

        return $this;

    }

    /**
     * Add content inside form hook and wrap with the TypeRocket container
     *
     * @param $post
     * @param $type
     */
    public function outputFormContent( $post, $type )
    {
        if ($post->post_type == $this->id) :

            $func = 'add_form_content_' . $this->id . '_' . $type;

            echo '<div class="typerocket-container">';

            $form = $this->getForm( $type );
            if (is_callable( $form )) {
                call_user_func( $form );
            } elseif (function_exists( $func )) {
                call_user_func( $func, $post );
            } elseif (TR_DEBUG == true) {
                echo "<div class=\"tr-dev-alert-helper\"><i class=\"icon tr-icon-bug\"></i> Add content here by defining: <code>function {$func}() {}</code></div>";
            }

            echo '</div>';


        endif;
    }

}
