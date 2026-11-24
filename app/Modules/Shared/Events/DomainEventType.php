<?php

namespace App\Modules\Shared\Events;

/**
 * Katalog event domain lintas modul.
 *
 * Prefix menandakan pemilik event (orgmate/eprogress/sentra) sehingga
 * konsumen dapat memetakan event ke layanan sumbernya saat modul dipisah.
 */
enum DomainEventType: string
{
    case OrganizationCreated = 'organization.created';
    case OrganizationUpdated = 'organization.updated';
    case OrganizationDeleted = 'organization.deleted';

    case UserRegistered = 'member.registered';
    case UserUpdated = 'member.updated';
    case UserRemoved = 'member.removed';

    case OrgmateMemberCreated = 'orgmate.member.created';
    case OrgmateMemberUpdated = 'orgmate.member.updated';
    case OrgmateMemberDeleted = 'orgmate.member.deleted';

    case OrgmatePositionCreated = 'orgmate.position.created';
    case OrgmatePositionUpdated = 'orgmate.position.updated';
    case OrgmatePositionDeleted = 'orgmate.position.deleted';

    case EProgressWorkProgramCreated = 'eprogress.work_program.created';
    case EProgressWorkProgramUpdated = 'eprogress.work_program.updated';
    case EProgressWorkProgramDeleted = 'eprogress.work_program.deleted';

    case EProgressActivityCreated = 'eprogress.activity.created';
    case EProgressActivityUpdated = 'eprogress.activity.updated';
    case EProgressActivityDeleted = 'eprogress.activity.deleted';

    case EProgressEvaluationCreated = 'eprogress.evaluation.created';
    case EProgressEvaluationUpdated = 'eprogress.evaluation.updated';
    case EProgressEvaluationDeleted = 'eprogress.evaluation.deleted';
    case EProgressLpjPublished = 'eprogress.lpj.published';

    case SentraBudgetCreated = 'sentra.budget.created';
    case SentraBudgetUpdated = 'sentra.budget.updated';
    case SentraBudgetDeleted = 'sentra.budget.deleted';

    case SentraDuesCreated = 'sentra.dues.created';
    case SentraDuesUpdated = 'sentra.dues.updated';
    case SentraDuesDeleted = 'sentra.dues.deleted';
    case SentraDuesPaid = 'sentra.dues.paid';

    case SentraCashTransactionCreated = 'sentra.cash_transaction.created';
    case SentraCashTransactionUpdated = 'sentra.cash_transaction.updated';
    case SentraCashTransactionDeleted = 'sentra.cash_transaction.deleted';
}
