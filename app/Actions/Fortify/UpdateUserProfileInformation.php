<?php

namespace App\Actions\Fortify;

use Modules\User\Entities\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'nombres'   => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'userID'    => ['required', 'string', 'max:255', Rule::unique('users', 'userID')->ignore($user->id)],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'nombres'   => $input['nombres'],
                'apellidos' => $input['apellidos'],
                'userID'    => $input['userID'],
                'email'     => $input['email'],
            ])->save();
        }
    }

    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'nombres'           => $input['nombres'],
            'apellidos'         => $input['apellidos'],
            'userID'            => $input['userID'],
            'email'             => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
