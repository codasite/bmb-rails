import * as React from 'react'
import { useEffect, useState } from 'react'
import { Modal } from '../Modal'
import { bracketApi } from '../../brackets/shared/api/bracketApi'
import { ActionButton } from '../../brackets/shared/components/ActionButtons'

interface MyTemplatesModalProps {
  tournamentsUrl: string
}

export const MyTemplatesModal = (props: MyTemplatesModalProps) => {
  const { tournamentsUrl } = props

  const [showModal, setShowModal] = useState(false)
  const [templateId, setTemplateId] = useState<number | null>(null)
  const [loading, setLoading] = useState(false)
  const [tournamentName, setTournamentName] = useState('')
  const [hasError, setHasError] = useState(false)
  const [editFormHeader, setEditFormHeader] = useState('')
  const [submitButtonText, setSubmitButtonText] = useState('')
  const [submitFunction, setSubmitFunction] = useState<() => void>(() => {})

  const handleHostTournamentClick = (e: any) => {
    const templateId = e.currentTarget.dataset.templateId
    setTemplateId(templateId)
    setEditFormHeader('Host tournament')
    setSubmitButtonText('Host')
    setSubmitFunction(onHostTournament)
    setShowModal(true)
  }

  const handleEditTemplateClick = (e: any) => {
    const templateId = e.currentTarget.dataset.templateId
    setEditFormHeader('Edit info')
    setSubmitButtonText('Save')
    setTemplateId(templateId)
    setSubmitFunction(onEditTemplate)
    setShowModal(true)
  }

  useEffect(() => {
    const buttons = document.getElementsByClassName(
      'wpbb-host-tournament-button'
    )
    if (buttons.length === 0) {
      return
    }
    for (const button of buttons) {
      button.addEventListener('click', handleHostTournamentClick)
    }
    return () => {
      for (const button of buttons) {
        button.removeEventListener('click', handleHostTournamentClick)
      }
    }
  }, [])

  useEffect(() => {
    const buttons = document.getElementsByClassName('wpbb-edit-template-button')
    if (buttons.length === 0) {
      return
    }
    for (const button of buttons) {
      button.addEventListener('click', handleEditTemplateClick)
    }
    return () => {
      for (const button of buttons) {
        button.removeEventListener('click', handleEditTemplateClick)
      }
    }
  }, [])

  const cancelButton = (
    <button
      onClick={() => setShowModal(false)}
      className="tw-bg-white/15 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 tw-p-12 tw-border-none hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-font-500 tw-cursor-pointer"
    >
      Cancel
    </button>
  )

  const onHostTournament = () => {
    if (!tournamentName) {
      setHasError(true)
      return
    }
    setLoading(true)
    bracketApi
      .createTournament({
        bracketTemplateId: templateId,
        title: tournamentName,
      })
      .then((res) => {
        window.location.href = tournamentsUrl
      })
      .catch((err) => {
        console.log(err)
        setLoading(false)
      })
  }

  const onEditTemplate = () => {
    if (!tournamentName) {
      setHasError(true)
      return
    }
    setLoading(true)
    bracketApi
      .editTemplate({
        bracketTemplateId: templateId,
        title: tournamentName,
      })
      .then((res) => {
        window.location.reload()
      })
      .catch((err) => {
        console.log(err)
        setLoading(false)
      })
  }

  return (
    <Modal show={showModal} setShow={setShowModal}>
      <div className="tw-flex tw-flex-col">
        <h1 className="tw-text-32 tw-leading-10 tw-font-white tw-whitespace-pre-line tw-mb-30">
          {editFormHeader}
        </h1>
        <input
          className={
            `tw-placeholder-${
              hasError ? 'red/60' : 'white/60'
            } tw-border-0 tw-border-b tw-border-white tw-mb-30 tw-border-solid tw-p-15 tw-outline-none tw-bg-transparent tw-text-16 tw-text-white tw-font-sans tw-w-full` +
            ' tw-uppercase' +
            (hasError ? ' tw-border-red tw-text-red' : '')
          }
          type="text"
          placeholder={
            hasError ? 'Tournament name is required' : 'My tournament name...'
          }
          value={tournamentName}
          onChange={(e) => {
            setTournamentName(e.target.value)
            setHasError(false)
          }}
        />
        <div className="tw-flex tw-flex-col tw-gap-10">
          <ActionButton
            variant="green"
            paddingY={12}
            paddingX={16}
            fontSize={16}
            fontWeight={700}
            disabled={loading}
            onClick={onHostTournament}
            className="hover:tw-text-white/75"
          >
            {submitButtonText}
          </ActionButton>
          {cancelButton}
        </div>
      </div>
    </Modal>
  )
}
