import * as React from 'react'
import { useState } from 'react'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { ModalHeader } from '../../ModalHeader'
import { ModalTextField } from '../../ModalTextFields'
import { CancelButton, ConfirmButton } from '../../ModalButtons'

export const EditTemplateModal = () => {
  const [templateId, setTemplateId] = useState<number | null>(null)
  const [loading, setLoading] = useState(false)
  const [title, setTitle] = useState('')
  const [titleHasError, setTitleHasError] = useState(false)
  const [date, setDate] = useState('')
  const [dateHasError, setDateHasError] = useState(false)
  const [show, setShow] = useState(false)
  addClickHandlers({
    buttonClassName: 'wpbb-edit-template-button',
    onButtonClick: (b) => {
      setTitle(b.dataset.templateTitle)
      setDate(b.dataset.templateDate)
      setTemplateId(parseInt(b.dataset.templateId))
      setShow(true)
    },
  })
  const onEditTemplate = () => {
    if (!title) {
      setTitleHasError(true)
      return
    }
    if (!date) {
      setDateHasError(true)
      return
    }
    setLoading(true)
    bracketApi
      .updateTemplate(templateId, {
        title: title,
        date: date,
      })
      .then((res) => {
        window.location.reload()
      })
      .catch((err) => {
        console.error(err)
        setLoading(false)
      })
  }
  return (
    <Modal show={show} setShow={setShow}>
      <div className="tw-flex tw-flex-col">
        <ModalHeader text={'Edit info'} />
        <div className="tw-flex tw-flex-col tw-gap-10">
          <ModalTextField
            hasError={titleHasError}
            errorText={'Template name is required'}
            placeholderText={'Template name...'}
            input={title}
            setInput={setTitle}
            setHasError={setTitleHasError}
          />
          <ModalTextField
            hasError={dateHasError}
            errorText={'Date is required'}
            placeholderText={'Date...'}
            input={date}
            setInput={setDate}
            setHasError={setDateHasError}
          />
          <div className={'tw-mb-30'}></div>
          <ConfirmButton
            disabled={loading || titleHasError}
            onClick={onEditTemplate}
          >
            {'Save'}
          </ConfirmButton>
          <CancelButton onClick={() => setShow(false)} />
        </div>
      </div>
    </Modal>
  )
}
