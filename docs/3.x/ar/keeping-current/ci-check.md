---
title: فحص CI الاختياري
description: فحص اختياري في GitHub Actions يتحقق من التوثيق في كل pull request، ويفشل أو يضيف تعليقًا حين يترك التغيير صفحاتٍ قديمة، من دون تشغيل وكيل ذكاء اصطناعي ومن دون كتابة أي شيء.
sources:
  - vendor/larecipe/larecipe/stubs/larecipe-docs-check.yml
  - vendor/larecipe/larecipe/stubs/ci/**
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/WriteCiCheckAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Enums/CiMode.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AffectedCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/AffectedFormatter.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/DTOs/AppFacts.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/PullCommand.php
translated_from: sha256:f94c2ea39322dae73461e4551c9097387dadc5fe725b358d396bdc037c281588
---

# فحص CI الاختياري

فحص CI شبكة أمان اختيارية لعمليات الـ push التي تجري من دون وكيل برمجة، أو من وكيل بلا خطّافات. ففي كل pull request يتحقق من التوثيق ويسرد الصفحات التي تغيّر الكود الذي تشرحه ولم تتغيّر هي. أضفه بالمثبّت، بأحد وضعين:

```bash
php artisan larecipe:install --ci=fail
php artisan larecipe:install --ci=comment
```

- **`fail`** يجعل الفحص يفشل، فيمكنك أن تشترط نجاحه قبل الدمج. أضف الوسم `skip-docs` إلى الـ pull request الذي لا يحتاج إلى تغيير في التوثيق، فينجح الفحص من دون push جديد.
- **`comment`** ينشر قائمة الصفحات القديمة تعليقًا على الـ pull request، ولا يفشل بسببها أبدًا.

وفي الحالتين، يُفشل أي خطأ تحقق الفحصَ. وهو يفحص فقط: لا يشغّل وكيل ذكاء اصطناعي، ولا يكتب أو يحفظ في commit أو يرفع شيئًا أبدًا، ولا يحتاج إلى أسرار (secrets) حين يكون التوثيق في المستودع، فهو آمن على الـ pull requests القادمة من النسخ المتفرّعة (forks). وكيل البرمجة يحدّث التوثيق قبل أن يرفع، وهذا الفحص يتأكد من أنه فعل.

## ما الذي يشغّله {#what-it-runs}

يكتب المثبّت الملف `.github/workflows/larecipe-docs-check.yml`. وخطوتا LaRecipe فيه هما:

```bash
php artisan larecipe:validate --json
php artisan larecipe:affected --base=origin/${{ github.base_ref }} --format=github --fail-on-stale --no-drift
```

يفحص [`larecipe:validate`](../operate/validation.md) الروابط والتنقل والمخططات (schemas) والمكوّنات والمقتطفات والمراجع الحية. ويقارن [`larecipe:affected`](how-it-works.md#larecipeaffected) الـ pull request بفرعه الأساسي ويحكم على هذا التغيير وحده: فالانحراف السابق للـ pull request مستبعَد (`--no-drift`).

وحين يوجد سير عمل بهذا الاسم أصلًا ويختلف عمّا كان LaRecipe سيكتبه، يتركه المثبّت كما هو: احذفه وشغّل المثبّت مرة أخرى لتحصل على نسخة جديدة.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="سير العمل كاملًا، خطوة بخطوة">

هذا سير العمل الذي يكتبه `--ci=fail` لمجموعة مستودع في تطبيق يتطلب PHP بالقيد `^8.3`. ويأتي إصدار PHP من قيد `php` في ملف `composer.json` لديك (8.3 على الأقل، و 8.4 حين لا يوجد قيد).

```yaml
# LaRecipe docs check (optional, no AI).
#
# Written by php artisan larecipe:install. On every pull request it validates
# the docs and lists pages whose code changed while the page did not. It never
# writes, commits or pushes, runs no AI agent and needs no secrets for docs in
# the repository, so it is safe on pull requests from forks. Your coding agent
# updates the docs before it pushes; this only checks that it did.
#
# Mode: fail. Add the skip-docs label to a pull request that needs no docs change.

name: LaRecipe docs check

on:
  pull_request:
    types: [opened, synchronize, reopened, labeled, unlabeled]

permissions:
  contents: read

concurrency:
  group: larecipe-docs-check-${{ github.event.pull_request.number }}
  cancel-in-progress: true

jobs:
  docs-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v5
        with:
          fetch-depth: 0

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: none

      - run: composer install --no-interaction --prefer-dist --no-progress

      - name: Prepare the app
        run: |
          if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi
          php artisan key:generate --force --no-interaction || true

      - name: Validate the docs
        run: php artisan larecipe:validate --json

      - name: Check the docs changed with the code
        if: ${{ !contains(github.event.pull_request.labels.*.name, 'skip-docs') }}
        run: php artisan larecipe:affected --base=origin/${{ github.base_ref }} --format=github --fail-on-stale --no-drift
```

1. **سحب المستودع بتاريخه الكامل.** يعطي `fetch-depth: 0` الأداة git الفرعَ الأساسي، فيرى `larecipe:affected` ما غيّره الـ pull request.
2. **تثبيت التطبيق وتجهيزه.** Composer، وملف `.env` من `.env.example`، ومفتاح للتطبيق. فأوامر LaRecipe تعمل داخل تطبيقك، ولا بد أن يعمل التطبيق.
3. **التحقق من التوثيق.** أي خطأ تحقق يُفشل الفحص في الوضعين.
4. **التأكد من أن التوثيق تغيّر مع الكود.** يقارن الـ pull request بفرعه الأساسي، `origin/${{ github.base_ref }}`.

يعمل سير العمل من جديد حين يُضاف وسم أو يُزال، فإضافة `skip-docs` تحوّل الفحص من الأحمر إلى الأخضر من دون push جديد.

</Accordion>

<Accordion title="مُخرجات وضع fail">

يحوّل `--format=github --fail-on-stale` كل صفحة قديمة إلى تعليق توضيحي (annotation) من نوع خطأ على ملف الصفحة، ويكتب تقرير Markdown في ملخص المهمة (job summary)، وينتهي بالرمز `1`:

```text
::error file=docs/billing/webhooks.md,title=Stale docs page: docs/billing/webhooks.md::This pull request changes code this page documents (app/Webhooks/Handler.php) but not the page. Update it, or add the skip-docs label when no docs change is needed.
LaRecipe: 1 docs page needs an update for this change.
```

اجعل الفحص إلزاميًا في قواعد حماية الفروع، فإما أن يحدّث كل pull request توثيقه، وإما أن يحمل الوسم `skip-docs` قرارًا ظاهرًا بأنه لا يحتاج إلى ذلك.

</Accordion>

<Accordion title="سير العمل والتعليق في وضع comment">

يعطي `--ci=comment` سير العمل صلاحية التعليق، ولا شيء غيرها:

```yaml
permissions:
  contents: read
  pull-requests: write   # to post the comment, nothing else
```

ويستبدل الخطوة الأخيرة بهاتين:

```yaml
      - name: List docs pages this pull request leaves stale
        id: affected
        run: php artisan larecipe:affected --base=origin/${{ github.base_ref }} --format=markdown --no-drift > larecipe-docs-check.md

      - name: Comment on the pull request
        # Pull requests from forks get a read-only token, so the comment is skipped there.
        if: ${{ github.event.pull_request.head.repo.full_name == github.repository }}
        continue-on-error: true
        env:
          GH_TOKEN: ${{ github.token }}
        run: |
          if [ "${{ steps.affected.outputs.stale }}" != "0" ]; then
            gh pr comment "${{ github.event.pull_request.number }}" --body-file larecipe-docs-check.md --edit-last \
              || gh pr comment "${{ github.event.pull_request.number }}" --body-file larecipe-docs-check.md
          else
            gh pr comment "${{ github.event.pull_request.number }}" --body-file larecipe-docs-check.md --edit-last || true
          fi
```

- يكتب `--format=markdown` التعليق ويضبط مُخرج الخطوة `stale` على عدد الصفحات القديمة.
- حين توجد صفحات قديمة، يعدّل سير العمل تعليقه الأخير، أو ينشر تعليقًا جديدًا. وحين لا توجد، يحدّث تعليقًا سابقًا ليقول ذلك، ولا ينشر شيئًا جديدًا.
- يستخدم التعليق `github.token` الخاص بسير العمل نفسه، لا سرًّا تضيفه أنت. وفي الـ pull requests القادمة من النسخ المتفرّعة يكون هذا الرمز للقراءة فقط، فتُتخطّى خطوة التعليق هناك.

ويبدو التعليق هكذا:

```markdown
### LaRecipe docs check

This pull request changes code that 1 docs page describes, and the page was not updated:

| Page | Sources | Changed files |
| --- | --- | --- |
| `docs/billing/webhooks.md` | `app/Webhooks/**` | `app/Webhooks/Handler.php` |

Update them in this pull request (the `larecipe-update-docs` skill does it in your agent session), or add the `skip-docs` label when no docs change is needed.
```

</Accordion>

<Accordion title="مجموعات قاعدة البيانات">

حين يحوي التطبيق [مجموعة قاعدة بيانات](../sources/database.md)، يحصل سير العمل على خطوة إضافية بعد تجهيز التطبيق. فهي تطبّق [ترحيلات التوثيق](../sources/docs-migrations.md) الخاصة بالـ pull request على قاعدة بيانات SQLite مؤقتة، فيُفشل أي ترحيل توثيق معطوب الفحصَ:

```yaml
      # Database doc sets (handbook): apply this pull request's docs migrations to a
      # throwaway database, so a broken docs migration fails the check.
      - name: Apply docs migrations
        run: |
          mkdir -p database && touch database/database.sqlite
          php artisan migrate --force --no-interaction

      # To compute stale pages against what is live, refresh the CI database from
      # production first with a read-only token (optional):
      # - run: php artisan larecipe:pull --set=handbook
```

ليربط الفحص التغييرات بالصفحات، يحتاج إلى صفحات المجموعة، مع قيم `sources:` الخاصة بها، في قاعدة بيانات CI. ومن دونها، يقول `larecipe:affected` إن المجموعة لا تحوي صفحات في قاعدة البيانات المحلية، ولا يُفحص إلا التحقق وترحيلات التوثيق. ولتحميلها، صدّر المجموعة على الإنتاج واسحب الحزمة في CI قبل الفحص: يحتاج `larecipe:pull` إلى `--from`، أي مسار تلك الحزمة.

```bash
php artisan larecipe:source:export --set=handbook --to=storage/handbook.tar.gz
php artisan larecipe:pull --set=handbook --from=storage/handbook.tar.gz
```

يعمل الأمر الأول على الإنتاج، والثاني في CI بعد وصول الحزمة. وطريقة وصول الحزمة إلى CI متروكة لك، وهذا هو الموضع الوحيد الذي يحتاج فيه فحص مجموعة قاعدة البيانات إلى الوصول إلى شيء خارج المستودع.

</Accordion>

<Accordion title="لماذا لا يكتب أبدًا">

وُجد الفحص ليلتقط تغيير توثيق ناقصًا، لا ليُجريه:

- **لا وكيل ذكاء اصطناعي.** يشغّل `larecipe:validate` و `larecipe:affected`، وهما يقرآن git والتوثيق ويعطيان الإجابة نفسها في كل مرة.
- **لا كتابة.** `contents: read` هي صلاحية المستودع الوحيدة. لا يحفظ شيئًا في commit ولا يرفع شيئًا. وفي وضع comment قد ينشر تعليقًا واحدًا، ولا شيء غيره.
- **لا أسرار.** مجموعة المستودع لا تحتاج إلى شيء غير الكود، فيعمل الفحص على الـ pull requests القادمة من النسخ المتفرّعة أيضًا.
- **لا pull requests لاحقة للتوثيق.** الـ pull request الخاص بالتوثيق الذي يُفتح بعد الدمج يصل إلى مراجع انتقل إلى غيره، ويبقى التوثيق خاطئًا في الإنتاج حتى يدمجه أحد. ولا يُنشئ LaRecipe مثل هذا الـ pull request.

مكان تغيير التوثيق هو الـ pull request نفسه الذي يحوي الكود، ويكتبه الوكيل الذي أجرى التغيير. راجع [كيف يعمل](how-it-works.md).

</Accordion>

</AccordionGroup>

## التالي {#next}

- [خطّاف pre-push](pre-push-hook.md): التقط الصفحات القديمة قبل الـ push، لا بعده.
- [التحقق و doctor](../operate/validation.md): ما الذي يفحصه `larecipe:validate`.
