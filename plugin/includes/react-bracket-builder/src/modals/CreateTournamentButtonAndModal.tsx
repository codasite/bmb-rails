import { ReactComponent as SignalIcon } from '../brackets/shared/assets/signal.svg'
import { ReactComponent as FileIcon } from '../brackets/shared/assets/file.svg'
import { ReactComponent as PlusIcon } from '../brackets/shared/assets/plus.svg'
import { ReactComponent as LogoDark } from '../brackets/shared/assets/logo_dark.svg'
import { ReactComponent as CheckIcon } from '../brackets/shared/assets/check.svg'
import * as React from 'react'
import { useState } from 'react'
import { Modal } from './Modal'

export const CreateTournamentButtonAndModal = (props: {
  myTemplatesUrl: string
  bracketTemplateBuilderUrl: string
  upgradeAccountUrl: string
  canCreateTournament: boolean
}) => {
  const [show, setShow] = useState(false)
  const cancelButton = (
    <button
      onClick={() => setShow(false)}
      className="tw-bg-white/15 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 tw-p-12 tw-border-none hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-font-500 tw-cursor-pointer"
    >
      Cancel
    </button>
  )
  return (
    <>
      <button
        className="tw-border-solid tw-border tw-border-white tw-bg-white/15 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 tw-p-16 hover:tw-bg-white hover:tw-text-black tw-font-sans tw-text-white tw-uppercase tw-w-full tw-cursor-pointer"
        onClick={() => setShow(true)}
      >
        <SignalIcon />
        <span className="tw-font-700 tw-text-24 ">Create Tournament</span>
      </button>
      <Modal show={show} setShow={setShow}>
        {props.canCreateTournament && (
          <div>
            <h1 className="tw-text-32 tw-leading-10 tw-text-center tw-font-white tw-whitespace-pre-line tw-mb-50">{`Host a tournament.
 invite & compete with friends.`}</h1>
            <a
              href={props.myTemplatesUrl}
              className="tw-border-solid tw-border tw-border-green tw-bg-green/20 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 md:tw-p-40 hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-20 tw-font-500 tw-mb-15 tw-p-20"
            >
              <FileIcon />
              <span>Use a template</span>
            </a>
            <a
              href={props.bracketTemplateBuilderUrl}
              className="tw-border-solid tw-border tw-border-white tw-bg-white/20 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 md:tw-p-40 hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-20 tw-font-500 tw-mb-15 tw-p-20"
            >
              <PlusIcon />
              <span>Start from scratch</span>
            </a>
            {cancelButton}
          </div>
        )}
        {!props.canCreateTournament && (
          <div className="tw-text-center">
            <LogoDark />
            <div className="tw-flex tw-items-end tw-justify-center tw-mt-24">
              <span className="tw-text-24">$</span>
              <span className="tw-text-64 tw-font-700 tw-leading-none">99</span>
            </div>
            <p className="tw-text-12 tw-font-700 tw-m-0 tw-mb-12">Yearly</p>
            <ul className="tw-list-none tw-p-0 tw-m-0 tw-text-16 tw-font-700">
              <li className="tw-flex tw-gap-10 tw-items-center tw-justify-center tw-py-4">
                <CheckIcon className="tw-text-red" />
                <span>Host and score tournaments</span>
              </li>
              <li className="tw-flex tw-gap-10 tw-items-center tw-justify-center tw-py-4">
                <CheckIcon className="tw-text-red" />
                <span>Invite and compete with friends</span>
              </li>
            </ul>
            <a
              href={props.upgradeAccountUrl}
              className="tw-border-solid tw-border tw-border-green tw-bg-green/15 tw-flex tw-items-center tw-justify-center tw-rounded-8 hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-leading-none tw-font-700 tw-mb-15 tw-mt-24 tw-p-12 tw-cursor-pointer"
            >
              Upgrade account
            </a>
            {cancelButton}
          </div>
        )}
      </Modal>
    </>
  )
}
