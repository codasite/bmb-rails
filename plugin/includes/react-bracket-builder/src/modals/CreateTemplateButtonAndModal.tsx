import { ReactComponent as SignalIcon } from '../brackets/shared/assets/signal.svg'
import { ReactComponent as FileIcon } from '../brackets/shared/assets/file.svg'
import { ReactComponent as PlusIcon } from '../brackets/shared/assets/plus.svg'
import { ReactComponent as LogoDark } from '../brackets/shared/assets/logo_dark.svg'
import { ReactComponent as CheckIcon } from '../brackets/shared/assets/check.svg'
import { ReactComponent as PencilIcon } from '../brackets/shared/assets/pencil-01.svg'
import * as React from 'react'
import { useState } from 'react'
import { Modal } from './Modal'

const EditTemplateForm = (props: {
  templateUrl: string
  onClose: () => void
}) => {
  const [name, setName] = useState('')
  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    const response = await fetch(props.templateUrl, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        // 'X-WP-Nonce': wpbb_ajax_obj.nonce,
      },
      body: JSON.stringify({
        title: name,
      }),
    })
    if (!response.ok) {
      throw new Error('Failed to edit template')
    }
    props.onClose()
    window.location.reload()
  }

  return (
    <form onSubmit={handleSubmit}>
      <input
        className="tw-border-none tw-border-b-solid tw-border-b tw-border-b-white tw-bg-transparent tw-text-white tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-20 tw-font-500 tw-p-20"
        type="text"
        placeholder="MY BRACKET TITLE..."
        value={name}
        onChange={(e) => setName(e.target.value)}
      />
      <button
        className="tw-border-solid tw-border tw-border-green tw-bg-green/15 tw-flex tw-items-center tw-justify-center hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-leading-none tw-font-700 tw-mb-15 tw-mt-24 tw-p-12 tw-cursor-pointer"
        type="submit"
      >
        Save
      </button>
    </form>
  )
}

export const CreateTemplateButtonAndModal = (props: {
  templateUrl: string
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
        className="tw-h-40 tw-w-40 tw-p-8 tw-bg-white/15 tw-border-none tw-text-white tw-flex tw-flex-col tw-items-center tw-justify-center tw-rounded-8 hover:tw-cursor-pointer hover:tw-bg-white hover:tw-text-black"
        onClick={() => setShow(true)}
      >
        <PencilIcon />
      </button>
      <Modal show={show} setShow={setShow}>
        <div>
          <h1 className="tw-text-32 tw-leading-10 tw-font-white tw-whitespace-pre-line tw-mb-50">{`Edit info`}</h1>
          <EditTemplateForm
            templateUrl={props.templateUrl}
            onClose={() => setShow(false)}
          />
          {cancelButton}
        </div>
      </Modal>
    </>
  )
}
