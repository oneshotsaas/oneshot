<?php

namespace OneShot\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use OneShot\Content\Models\Category;
use OneShot\Content\Models\Item;
use OneShot\Content\Models\Tag;
use OneShot\Content\Services\Resolver;

class ContentSeedDemo extends BaseCommand
{
    protected $group       = 'OneShot';
    protected $name        = 'content:seed-demo';
    protected $description = 'Seed demo categories, tags, posts and pages for visual testing';

    public function run(array $params): void
    {
        helper('oneshot');

        $db = \Config\Database::connect();
        $db->query('SET FOREIGN_KEY_CHECKS=0');
        $db->query('TRUNCATE content_item_tags');
        $db->query('TRUNCATE content_item_categories');
        $db->query('TRUNCATE content_tags');
        $db->query('TRUNCATE content_items');
        $db->query('TRUNCATE content_categories');
        $db->query('SET FOREIGN_KEY_CHECKS=1');
        CLI::write('Tables cleared.', 'yellow');

        $cats  = new Category();
        $items = new Item();
        $tags  = new Tag();

        // ── Categories ──────────────────────────────────────────────

        $catBlog = $cats->add([
            'parent_id'        => null,
            'title'            => 'Blog',
            'slug'             => 'blog',
            'meta_title'       => 'Blog — OneShot',
            'meta_description' => 'Articles, tutorials and updates from the OneShot team.',
            'content'          => null,
            'sort'             => 10,
            'is_active'        => 1,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        CLI::write("Category: Blog (id={$catBlog})", 'green');

        $catAI = $cats->add([
            'parent_id'        => $catBlog,
            'title'            => 'AI',
            'slug'             => 'ai',
            'meta_title'       => 'AI — Blog',
            'meta_description' => 'Artificial intelligence articles and experiments.',
            'content'          => null,
            'sort'             => 10,
            'is_active'        => 1,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        CLI::write("Category: AI (id={$catAI})", 'green');

        $catDev = $cats->add([
            'parent_id'        => $catBlog,
            'title'            => 'Dev',
            'slug'             => 'dev',
            'meta_title'       => 'Dev — Blog',
            'meta_description' => 'Development tips, patterns and tools.',
            'content'          => null,
            'sort'             => 20,
            'is_active'        => 1,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        CLI::write("Category: Dev (id={$catDev})", 'green');

        $catLegal = $cats->add([
            'parent_id'        => null,
            'title'            => 'Legal',
            'slug'             => 'legal',
            'meta_title'       => 'Legal — OneShot',
            'meta_description' => 'Privacy policy, terms of service and other legal documents.',
            'content'          => null,
            'sort'             => 20,
            'is_active'        => 1,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        CLI::write("Category: Legal (id={$catLegal})", 'green');

        // ── Tags ─────────────────────────────────────────────────────

        $tagAI = $tags->add([
            'title'      => 'AI',
            'slug'       => 'ai-tag',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $tagDev = $tags->add([
            'title'      => 'Dev',
            'slug'       => 'dev-tag',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $tagTutorial = $tags->add([
            'title'      => 'Tutorial',
            'slug'       => 'tutorial',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $tagNews = $tags->add([
            'title'      => 'News',
            'slug'       => 'news',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        CLI::write("Tags: AI, Dev, Tutorial, News", 'green');

        // ── Posts ────────────────────────────────────────────────────

        $postContent1 = json_encode([
            'time'    => time() * 1000,
            'version' => '2.28.2',
            'blocks'  => [
                ['type' => 'header',    'data' => ['text' => 'What is a large language model?', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'A large language model (LLM) is a type of artificial intelligence trained on massive text datasets. It learns statistical patterns in language and uses them to generate, summarize, translate, and reason about text.']],
                ['type' => 'header',    'data' => ['text' => 'How training works', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Training involves feeding billions of tokens through a transformer architecture and adjusting weights via gradient descent. The result is a model that can predict the next token with high accuracy across a wide range of topics.']],
                ['type' => 'list',      'data' => ['style' => 'unordered', 'items' => ['Transformer architecture', 'Self-supervised pre-training', 'Instruction fine-tuning (RLHF)', 'Context window and attention mechanism']]],
                ['type' => 'header',    'data' => ['text' => 'Practical applications', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'LLMs are used for code generation, document summarization, customer support automation, and creative writing assistance. They integrate with external tools via function calling and retrieval-augmented generation (RAG).']],
                ['type' => 'quote',     'data' => ['text' => 'The models do not understand language the way humans do — they model the distribution of tokens.', 'caption' => 'Andrej Karpathy', 'alignment' => 'left']],
                ['type' => 'header',    'data' => ['text' => 'Limitations', 'level' => 3]],
                ['type' => 'list',      'data' => ['style' => 'ordered', 'items' => ['Hallucinations — confident wrong answers', 'Knowledge cutoff date', 'High inference cost at scale', 'Sensitivity to prompt wording']]],
                ['type' => 'paragraph', 'data' => ['text' => 'Despite these limitations, LLMs are already the most versatile AI tools available and continue to improve rapidly with each generation.']],
            ],
        ]);

        $postContent2 = json_encode([
            'time'    => time() * 1000,
            'version' => '2.28.2',
            'blocks'  => [
                ['type' => 'header',    'data' => ['text' => 'Why prompt engineering matters', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'The same model can produce wildly different outputs depending on how the prompt is structured. Prompt engineering is the practice of designing inputs that reliably produce useful, accurate, and well-formatted responses.']],
                ['type' => 'header',    'data' => ['text' => 'Core techniques', 'level' => 2]],
                ['type' => 'list',      'data' => ['style' => 'unordered', 'items' => ['Zero-shot prompting — ask directly without examples', 'Few-shot prompting — provide 2–5 examples in the prompt', 'Chain-of-thought — ask the model to reason step by step', 'System prompt — set persona, tone, and constraints upfront', 'Output format instructions — JSON, Markdown, plain text']]],
                ['type' => 'header',    'data' => ['text' => 'Zero-shot vs few-shot', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Zero-shot is fast and good for simple tasks. Few-shot dramatically improves accuracy on structured outputs, classification, and domain-specific tasks where the model needs to match a specific pattern.']],
                ['type' => 'quote',     'data' => ['text' => 'Let us think step by step.', 'caption' => 'Chain-of-thought trigger phrase', 'alignment' => 'left']],
                ['type' => 'header',    'data' => ['text' => 'Common mistakes', 'level' => 3]],
                ['type' => 'list',      'data' => ['style' => 'ordered', 'items' => ['Vague instructions without success criteria', 'No output format specification', 'Overloading a single prompt with multiple unrelated tasks', 'Ignoring system prompt for context-setting']]],
                ['type' => 'paragraph', 'data' => ['text' => 'Good prompts are concise, specific, and include examples of the expected output format. Iterate quickly — even small wording changes can have a large impact on output quality.']],
            ],
        ]);

        $postContent3 = json_encode([
            'time'    => time() * 1000,
            'version' => '2.28.2',
            'blocks'  => [
                ['type' => 'header',    'data' => ['text' => 'What is RAG?', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Retrieval-Augmented Generation (RAG) combines a vector database with an LLM. Instead of relying solely on parametric memory baked into model weights, RAG retrieves relevant documents at query time and injects them into the prompt as context.']],
                ['type' => 'header',    'data' => ['text' => 'Architecture overview', 'level' => 2]],
                ['type' => 'list',      'data' => ['style' => 'ordered', 'items' => ['User sends a query', 'Query is embedded into a vector', 'Top-K similar chunks retrieved from vector DB', 'Chunks injected into the LLM prompt as context', 'LLM generates a grounded answer']]],
                ['type' => 'header',    'data' => ['text' => 'When to use RAG', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'RAG is ideal when you need answers grounded in private, frequently updated, or domain-specific documents — product manuals, legal contracts, internal knowledge bases, or support tickets.']],
                ['type' => 'quote',     'data' => ['text' => 'RAG trades model capacity for document relevance. The model knows less, but what it retrieves is always fresh.', 'caption' => '', 'alignment' => 'left']],
                ['type' => 'header',    'data' => ['text' => 'Chunking strategy', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Chunk size and overlap dramatically affect retrieval quality. Fixed-size chunks of 512 tokens with 10% overlap are a common starting point. Semantic chunking — splitting on paragraph or section boundaries — improves coherence.']],
            ],
        ]);

        $postContent4 = json_encode([
            'time'    => time() * 1000,
            'version' => '2.28.2',
            'blocks'  => [
                ['type' => 'header',    'data' => ['text' => 'PHP 8.3 — what is new', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'PHP 8.3 shipped with several quality-of-life improvements: typed class constants, readonly properties on anonymous classes, the json_validate() function, and more granular DateTime exceptions.']],
                ['type' => 'header',    'data' => ['text' => 'Typed class constants', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'You can now declare a type for class constants. This prevents accidental type coercion when the constant is accessed via an interface or inherited by a child class.']],
                ['type' => 'list',      'data' => ['style' => 'unordered', 'items' => ['const string VERSION = \'1.0.0\';', 'const int MAX_RETRIES = 3;', 'Enforced at compile time, not only at runtime']]],
                ['type' => 'header',    'data' => ['text' => 'json_validate()', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'The new json_validate() function checks whether a string is valid JSON without decoding it. This is significantly faster than json_decode() + checking for null when you only need to validate.']],
                ['type' => 'quote',     'data' => ['text' => 'If you only need to validate, do not decode. json_validate() is 2–5x faster on large payloads.', 'caption' => '', 'alignment' => 'left']],
                ['type' => 'header',    'data' => ['text' => 'Migration notes', 'level' => 2]],
                ['type' => 'list',      'data' => ['style' => 'ordered', 'items' => ['Run static analysis before upgrading', 'Check for deprecated dynamic property usage', 'Update Composer dependencies to 8.3-compatible versions', 'Re-run test suite — most codebases pass without changes']]],
            ],
        ]);

        $postContent5 = json_encode([
            'time'    => time() * 1000,
            'version' => '2.28.2',
            'blocks'  => [
                ['type' => 'header',    'data' => ['text' => 'Why index design matters', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Poorly indexed tables are the single most common source of slow queries in web applications. A query that scans 10 million rows instead of using a covering index can be 1000x slower than necessary.']],
                ['type' => 'header',    'data' => ['text' => 'Index types in MySQL', 'level' => 2]],
                ['type' => 'list',      'data' => ['style' => 'unordered', 'items' => ['B-tree index — default, good for equality and range queries', 'Composite index — covers multiple columns, order matters', 'Covering index — includes all columns the query needs', 'Partial index — filters rows, reduces index size', 'Full-text index — for MATCH AGAINST searches']]],
                ['type' => 'header',    'data' => ['text' => 'Composite index column order', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'Put the most selective column first. For queries with both equality and range conditions, equality columns come first. MySQL can only use the leftmost prefix of a composite index.']],
                ['type' => 'quote',     'data' => ['text' => 'An index not used is worse than no index — it wastes write overhead and misleads developers reading EXPLAIN output.', 'caption' => '', 'alignment' => 'left']],
                ['type' => 'header',    'data' => ['text' => 'Using EXPLAIN', 'level' => 3]],
                ['type' => 'list',      'data' => ['style' => 'ordered', 'items' => ['Run EXPLAIN SELECT ... to see query plan', 'Check type column: ref/range is good, ALL is bad', 'key column shows which index MySQL chose', 'rows column shows estimated scan size', 'Extra: Using index = covering index hit']]],
            ],
        ]);

        $privacyContent = json_encode([
            'time'    => time() * 1000,
            'version' => '2.28.2',
            'blocks'  => [
                ['type' => 'header',    'data' => ['text' => 'Information we collect', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'We collect information you provide directly — name, email address, and payment details when you create an account or subscribe. We also collect usage data automatically: pages visited, features used, browser type, and approximate location derived from IP address.']],
                ['type' => 'header',    'data' => ['text' => 'How we use it', 'level' => 2]],
                ['type' => 'list',      'data' => ['style' => 'unordered', 'items' => ['To provide and operate the service', 'To send transactional emails (receipts, password resets)', 'To improve product features based on aggregate usage patterns', 'To prevent fraud and abuse']]],
                ['type' => 'header',    'data' => ['text' => 'Data retention', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Account data is retained for the duration of your subscription plus 30 days after cancellation. You may request deletion at any time by contacting support. Anonymized aggregate analytics data may be retained indefinitely.']],
                ['type' => 'header',    'data' => ['text' => 'Third parties', 'level' => 2]],
                ['type' => 'list',      'data' => ['style' => 'unordered', 'items' => ['Stripe — payment processing', 'AWS — infrastructure and file storage', 'Postmark — transactional email delivery']]],
                ['type' => 'paragraph', 'data' => ['text' => 'We do not sell your personal data to third parties. Sub-processors receive only the minimum data necessary to provide their service.']],
                ['type' => 'header',    'data' => ['text' => 'Your rights', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Depending on your jurisdiction you may have the right to access, correct, delete, or export your personal data. To exercise any of these rights, contact privacy@example.com.']],
            ],
        ]);

        $termsContent = json_encode([
            'time'    => time() * 1000,
            'version' => '2.28.2',
            'blocks'  => [
                ['type' => 'header',    'data' => ['text' => 'Acceptance of terms', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'By accessing or using OneShot you agree to be bound by these Terms of Service. If you do not agree, do not use the service. These terms apply to all users including free-tier and paid subscribers.']],
                ['type' => 'header',    'data' => ['text' => 'Account responsibility', 'level' => 2]],
                ['type' => 'list',      'data' => ['style' => 'unordered', 'items' => ['You are responsible for keeping your credentials secure', 'You must be 18 years of age or older to create an account', 'One person or legal entity may not maintain more than one free account', 'You are responsible for all activity that occurs under your account']]],
                ['type' => 'header',    'data' => ['text' => 'Prohibited uses', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'You may not use OneShot to transmit spam, malware, or illegal content. You may not attempt to reverse-engineer, decompile, or scrape the service. Automated access without prior written consent is prohibited.']],
                ['type' => 'header',    'data' => ['text' => 'Payment and refunds', 'level' => 2]],
                ['type' => 'list',      'data' => ['style' => 'ordered', 'items' => ['Subscriptions are billed monthly or annually in advance', 'All charges are non-refundable except as required by law', 'Downgrade takes effect at the end of the current billing period', 'Prices may change with 30 days written notice']]],
                ['type' => 'header',    'data' => ['text' => 'Limitation of liability', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'OneShot is provided "as is" without warranties of any kind. Our liability to you for any damages arising out of or related to these terms is limited to the amount you paid us in the twelve months preceding the claim.']],
                ['type' => 'header',    'data' => ['text' => 'Governing law', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'These terms are governed by the laws of the State of Delaware, United States. Any disputes shall be resolved in the courts of Delaware, USA.']],
            ],
        ]);

        // ── Insert items ──────────────────────────────────────────────

        $now = date('Y-m-d H:i:s');

        $demo = '/assets/content/demo/';

        $post1 = $items->add([
            'type'                  => 'post',
            'title'                 => 'Introduction to Large Language Models',
            'slug'                  => 'introduction-to-large-language-models',
            'canonical_category_id' => $catAI,
            'image'                 => $demo . 'ai-llm.svg',
            'meta_title'            => 'Introduction to Large Language Models — Blog',
            'meta_description'      => 'A practical overview of what LLMs are, how they are trained, and where they are applied.',
            'content'               => $postContent1,
            'template'              => 'post',
            'is_active'             => 1,
            'created_at'            => $now,
            'updated_at'            => $now,
        ]);

        $post2 = $items->add([
            'type'                  => 'post',
            'title'                 => 'Prompt Engineering: Techniques That Work',
            'slug'                  => 'prompt-engineering-techniques-that-work',
            'canonical_category_id' => $catAI,
            'image'                 => $demo . 'prompt-engineering.svg',
            'meta_title'            => 'Prompt Engineering Techniques — Blog',
            'meta_description'      => 'Zero-shot, few-shot, chain-of-thought — learn which prompting strategy fits your use case.',
            'content'               => $postContent2,
            'template'              => 'post',
            'is_active'             => 1,
            'created_at'            => date('Y-m-d H:i:s', strtotime('-3 days')),
            'updated_at'            => date('Y-m-d H:i:s', strtotime('-3 days')),
        ]);

        $post3 = $items->add([
            'type'                  => 'post',
            'title'                 => 'Building a RAG Pipeline from Scratch',
            'slug'                  => 'building-rag-pipeline-from-scratch',
            'canonical_category_id' => $catAI,
            'image'                 => $demo . 'rag-pipeline.svg',
            'meta_title'            => 'Building a RAG Pipeline — Blog',
            'meta_description'      => 'Step-by-step guide to retrieval-augmented generation: vector DB, chunking, and prompt injection.',
            'content'               => $postContent3,
            'template'              => 'post',
            'is_active'             => 1,
            'created_at'            => date('Y-m-d H:i:s', strtotime('-7 days')),
            'updated_at'            => date('Y-m-d H:i:s', strtotime('-7 days')),
        ]);

        $post4 = $items->add([
            'type'                  => 'post',
            'title'                 => 'PHP 8.3 — New Features and Migration Guide',
            'slug'                  => 'php-83-new-features-migration-guide',
            'canonical_category_id' => $catDev,
            'image'                 => $demo . 'php-83.svg',
            'meta_title'            => 'PHP 8.3 New Features — Blog',
            'meta_description'      => 'Typed class constants, json_validate(), readonly anonymous classes and more.',
            'content'               => $postContent4,
            'template'              => 'post',
            'is_active'             => 1,
            'created_at'            => date('Y-m-d H:i:s', strtotime('-5 days')),
            'updated_at'            => date('Y-m-d H:i:s', strtotime('-5 days')),
        ]);

        $post5 = $items->add([
            'type'                  => 'post',
            'title'                 => 'MySQL Index Design for Web Developers',
            'slug'                  => 'mysql-index-design-for-web-developers',
            'canonical_category_id' => $catDev,
            'image'                 => $demo . 'mysql-index.svg',
            'meta_title'            => 'MySQL Index Design — Blog',
            'meta_description'      => 'B-tree, composite, and covering indexes explained. How to read EXPLAIN and stop slow queries.',
            'content'               => $postContent5,
            'template'              => 'post',
            'is_active'             => 1,
            'created_at'            => date('Y-m-d H:i:s', strtotime('-10 days')),
            'updated_at'            => date('Y-m-d H:i:s', strtotime('-10 days')),
        ]);

        $pagePrivacy = $items->add([
            'type'                  => 'page',
            'title'                 => 'Privacy Policy',
            'slug'                  => 'privacy',
            'canonical_category_id' => $catLegal,
            'meta_title'            => 'Privacy Policy — OneShot',
            'meta_description'      => 'How OneShot collects, uses, and protects your personal data.',
            'content'               => $privacyContent,
            'template'              => 'page',
            'is_active'             => 1,
            'created_at'            => $now,
            'updated_at'            => $now,
        ]);

        $pageTerms = $items->add([
            'type'                  => 'page',
            'title'                 => 'Terms of Service',
            'slug'                  => 'terms',
            'canonical_category_id' => null,
            'meta_title'            => 'Terms of Service — OneShot',
            'meta_description'      => 'Terms and conditions governing your use of OneShot.',
            'content'               => $termsContent,
            'template'              => 'page',
            'is_active'             => 1,
            'created_at'            => $now,
            'updated_at'            => $now,
        ]);

        CLI::write("Posts: {$post1}, {$post2}, {$post3}, {$post4}, {$post5}", 'green');
        CLI::write("Pages: privacy={$pagePrivacy}, terms={$pageTerms}", 'green');

        // ── Item ↔ Category pivots ────────────────────────────────────

        $items->syncCategories($post1,       [$catAI]);
        $items->syncCategories($post2,       [$catAI]);
        $items->syncCategories($post3,       [$catAI]);
        $items->syncCategories($post4,       [$catDev]);
        $items->syncCategories($post5,       [$catDev]);
        $items->syncCategories($pagePrivacy, [$catLegal]);

        // ── Item ↔ Tag pivots ─────────────────────────────────────────

        $items->syncTags($post1, [$tagAI, $tagTutorial]);
        $items->syncTags($post2, [$tagAI, $tagTutorial]);
        $items->syncTags($post3, [$tagAI]);
        $items->syncTags($post4, [$tagDev, $tagNews]);
        $items->syncTags($post5, [$tagDev, $tagTutorial]);

        // ── Flush content cache ───────────────────────────────────────

        cache()->delete(config('Content')->cacheKey ?? 'content_tree_v1');

        CLI::write('Content cache flushed.', 'yellow');
        CLI::write('Demo content seeded successfully.', 'green');
    }
}
